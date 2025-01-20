<?php

namespace App\Http\Controllers\Webhooks;

use App\Exceptions\OperationFailedException;
use App\Libraries\RedsysAPI;
use App\Models\CoursesPaymentsModel;
use App\Models\CoursesPaymentTermsUsersModel;
use App\Models\CoursesStudentsModel;
use App\Models\EducationalProgramsPaymentsModel;
use App\Models\EducationalProgramsPaymentTermsUsersModel;
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

        // Si el pago ha sido denegado
        $this->validatePayment($decodedData);

        // Datos de pago. Si es de curso o programa formativo y si es plazo o pago único
        $paymentData = json_decode(urldecode($decodedData->Ds_MerchantData));
        $orderNumber = $decodedData->Ds_Order;

        $this->processPayment($paymentData, $orderNumber, $decodedData);

        return response()->json(["message" => "OK"]);
    }

    private function decodeData($data)
    {
        $decodedData = $this->redsysAPI->decodeMerchantParameters($data);
        return json_decode($decodedData);
    }

    // Si el pago ha sido denegado
    private function validatePayment($decodedData)
    {
        $dsResponse = intval($decodedData->Ds_Response);
        if ($dsResponse > 99) {
            throw new OperationFailedException("Error en el pago", 406);
        }
    }


    private function processPayment($paymentData, $orderNumber, $decodedData)
    {
        DB::transaction(function () use ($paymentData, $orderNumber, $decodedData) {
            if ($paymentData->learningObjectType == "course") {
                if ($paymentData->paymentType == "singlePayment") {
                    $this->saveCourseEnrolled($orderNumber, $decodedData);
                } else if ($paymentData->paymentType == "paymentTerm") {
                    $this->savePaymentTerm($orderNumber, $decodedData, "course");
                }
            } else if ($paymentData->learningObjectType == "educationalProgram") {
                if ($paymentData->paymentType == "singlePayment") {
                    $this->saveEducationalProgramEnrolled($orderNumber, $decodedData);
                } else if ($paymentData->paymentType == "paymentTerm") {
                    $this->savePaymentTerm($orderNumber, $decodedData, "educationalProgram");
                }
            }
        });
    }

    private function savePaymentTerm($orderNumber, $decodedData, $learningObjectType)
    {
        $model = $learningObjectType == "course" ? CoursesPaymentTermsUsersModel::class : EducationalProgramsPaymentTermsUsersModel::class;

        $paymentTerm = $model::where('order_number', $orderNumber)->first();

        if ($paymentTerm->is_paid) {
            return;
        }
        $paymentTerm->is_paid = true;
        $paymentTerm->info = json_encode($decodedData);
        $paymentTerm->payment_date = now();
        $paymentTerm->save();
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

        if ($firma != $signature) {
            abort(401);
        }
    }
}
