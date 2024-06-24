<?php

namespace App\Http\Controllers\Webhooks;

use App\Libraries\RedsysAPI;
use App\Models\CoursesPaymentsModel;
use App\Models\CoursesStudentsModel;
use App\Models\EducationalProgramsPaymentsModel;
use App\Models\EducationalProgramsStudentsModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcessPaymentRedsys extends BaseController
{
    private $redsysAPI;

    public function __construct(RedsysAPI $redsysAPI)
    {
        $this->redsysAPI = $redsysAPI;
    }

    public function index(Request $request)
    {
        $data = $request->input("Ds_MerchantParameters");
        $signature = $request->input("Ds_Signature");

        $this->validateSignature($data, $signature);

        $decodedData = $this->decodeData($data);

        $order_number = $decodedData->Ds_Order;
        $learning_object_type = $decodedData->Ds_MerchantData;

        $this->processPayment($learning_object_type, $order_number, $decodedData);

        return response()->json(["message" => "OK"]);
    }

    private function decodeData($data)
    {
        $decodedData = $this->redsysAPI->decodeMerchantParameters($data);
        return json_decode($decodedData);
    }

    private function processPayment($learning_object_type, $order_number, $decodedData)
    {
        DB::transaction(function () use ($learning_object_type, $order_number, $decodedData) {
            if ($learning_object_type == "course") {
                $this->saveCourseEnrolled($order_number, $decodedData);
            } else if ($learning_object_type == "educational_program") {
                $this->saveEducationalProgramEnrolled($order_number, $decodedData);
            }
        });
    }

    private function saveCourseEnrolled($orderNumber, $decodedData)
    {
        // Cambiamos el estado del pago a pagado
        $coursePayment = CoursesPaymentsModel::where('order_number', $orderNumber)->first();
        $coursePayment->is_paid = 1;
        $coursePayment->info = json_encode($decodedData);
        $coursePayment->save();

        // Extraemos la inscripción del curso y el estudiante y la ponemos como matriculado
        $courseStudent = CoursesStudentsModel::where('user_uid', $coursePayment->user_uid)
            ->where('course_uid', $coursePayment->course_uid)
            ->first();

        $courseStudent->status = "ENROLLED";
        $courseStudent->save();
    }

    private function saveEducationalProgramEnrolled($orderNumber, $decodedData)
    {
        $educationalProgramPayment = EducationalProgramsPaymentsModel::where('order_number', $orderNumber)->first();
        $educationalProgramPayment->is_paid = 1;
        $educationalProgramPayment->info = json_encode($decodedData);
        $educationalProgramPayment->save();

        // Extraemos la inscripción del programa y el estudiante y la ponemos como matriculado
        $educationalProgramStudent = EducationalProgramsStudentsModel::where('user_uid', $educationalProgramPayment->user_uid)
            ->where('educational_program_uid', $educationalProgramPayment->educational_program_uid)
            ->first();

        $educationalProgramStudent->status = "ENROLLED";

        $educationalProgramStudent->save();
    }

    // Comprobamos que la firma es correcta
    private function validateSignature($data, $signature)
    {
        $kc = app('general_options')['redsys_encryption_key'];
        $firma = $this->redsysAPI->createMerchantSignatureNotif($kc, $data);

        if ($firma != $signature) abort(401);
    }
}
