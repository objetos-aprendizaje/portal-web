<?php

namespace App\Http\Controllers;

use App\Exceptions\OperationFailedException;
use App\Models\CoursesModel;
use Illuminate\Routing\Controller as BaseController;
use App\Libraries\RedsysAPI;
use App\Models\CoursesPaymentsModel;
use App\Models\CoursesStudentsModel;
use App\Models\EducationalProgramsModel;
use App\Models\EducationalProgramsStudentsModel;
use App\Models\EducationalProgramStatusesModel;
use Illuminate\Http\Request;

class CartController extends BaseController
{
    public function index($learning_object_type, $uid)
    {

        if (!in_array($learning_object_type, ["course", "educational_program"])) {
            return abort(405);
        }

        if ($learning_object_type == "course") {
            $learningObjectData = $this->getCourseData($uid);
        } else if ($learning_object_type == "educational_program") {
            $learningObjectData = $this->getEducationalProgramData($uid);
        }

        if ($learningObjectData["cost"] && $learningObjectData["cost"] > 0 && !app('general_options')['redsys_enabled']) {
            return abort(405);
        }

        return view("cart", [
            "resources" => [
                "resources/js/cart.js"
            ],
            "learning_object_type" => $learning_object_type,
            "learning_object_uid" => $uid,
            "learningObjectData" => $learningObjectData,
            "page_title" => "Gestión de pago"
        ]);
    }

    protected function getCourseData($uid)
    {
        $course = CoursesModel::where('uid', $uid)->with(['course_documents', 'paymentTerms'])->first();

        $totalCost = 0;
        if($course->payment_mode == "INSTALLMENT_PAYMENT") {
            foreach ($course->paymentTerms as $paymentTerm) {
                $totalCost += $paymentTerm->cost;
            }
        } else {
            $totalCost = $course->cost;
        }

        return [
            "uid" => $course->uid,
            "title" => $course->title,
            "description" => $course->description,
            "cost" => $totalCost,
            "ects_workload" => $course->ects_workload,
            "image_path" => $course->image_path,
        ];
    }

    protected function getEducationalProgramData($uid)
    {
        $educational_program = EducationalProgramsModel::where('uid', $uid)->with(['courses', 'paymentTerms'])->first();

        $totalCost = 0;
        if($educational_program->payment_mode == "INSTALLMENT_PAYMENT") {
            foreach ($educational_program->paymentTerms as $paymentTerm) {
                $totalCost += $paymentTerm->cost;
            }
        } else {
            $totalCost = $educational_program->cost;
        }

        $ectsWorkload = 0;
        foreach ($educational_program->courses as $course) {
            $ectsWorkload += $course->ects_workload;
        }

        $data = [
            "uid" => $educational_program->uid,
            "title" => $educational_program->name,
            "description" => $educational_program->description,
            "cost" => $totalCost,
            "ects_workload" => $ectsWorkload,
            "image_path" => $educational_program->image_path,
        ];

        return $data;
    }

    public function inscribe(Request $request)
    {
        $learningObjectType = $request->input('learningObjectType');
        $learningObjectUid = $request->input('learningObjectUid');

        $this->validateRequest($learningObjectType);

        if ($learningObjectType == "course") {
            // Comprobamos primero que no esté inscrito
            $isInscribed = CoursesStudentsModel::where('user_uid', auth()->user()->uid)->where('course_uid', $learningObjectUid)->exists();
            if($isInscribed) throw new OperationFailedException("Ya estás inscrito");

            $learningObject = CoursesModel::where('uid', $learningObjectUid)->with(['status', 'students'])->first();
            $statusWithLearningObject = $this->inscribeUserInCourse($learningObject);
        } else if ($learningObjectType == "educational_program") {
            $isInscribed = EducationalProgramsStudentsModel::where('user_uid', auth()->user()->uid)->where('educational_program_uid', $learningObjectUid)->exists();
            if($isInscribed) throw new OperationFailedException("Ya estás inscrito");

            $learningObject = EducationalProgramsModel::where('uid', $learningObjectUid)->with(['status', 'students'])->first();
            $statusWithLearningObject = $this->inscribeUserInEducationalProgram($learningObject);
        }

        $this->validateLearningObject($learningObject);
        $this->checkIfUserIsAlreadyInscribed($learningObject);

        return response()->json(['statusWithLearningObject' => $statusWithLearningObject, 'message' => 'Inscripción realizada con éxito'], 200);
    }


    private function validateLearningObject($learningObject)
    {
        // Si el objeto de aprendizaje no existe o su estado no es "INSCRIPTION", aborta la ejecución
        if (!$learningObject || $learningObject->status->code != "INSCRIPTION") {
            throw new OperationFailedException("El curso no está disponible para inscripción");
        }
    }

    private function checkIfUserIsAlreadyInscribed($learningObject)
    {
        $userUid = auth()->user()->uid;

        $isInscribed = $learningObject->students->contains('uid', $userUid);

        if ($isInscribed) {
            throw new OperationFailedException("Ya estás inscrito");
        }
    }

    private function inscribeUserInCourse($course)
    {

        if (!$course->cost && !$course->validate_student_registrations) {
            $statusInscription = "ENROLLED";
        } else {
            $statusInscription = "INSCRIBED";
        }

        CoursesStudentsModel::insert([
            'uid' => generate_uuid(),
            'user_uid' => auth()->user()->uid,
            'course_uid' => $course->uid,
            'acceptance_status' => $course->validate_student_registrations ? "PENDING" : "ACCEPTED",
            'status' => $statusInscription
        ]);

        return $statusInscription;
    }

    private function inscribeUserInEducationalProgram($educational_program)
    {
        if (!$educational_program->cost && !$educational_program->validate_student_registrations) {
            $statusInscription = "ENROLLED";
        } else {
            $statusInscription = "INSCRIBED";
        }

        EducationalProgramsStudentsModel::insert([
            'uid' => generate_uuid(),
            'user_uid' => auth()->user()->uid,
            'educational_program_uid' => $educational_program->uid,
            'acceptance_status' => $educational_program->validate_student_registrations ? "PENDING" : "ACCEPTED",
            'status' => $statusInscription
        ]);

        return $statusInscription;
    }

    public function makePayment(Request $request)
    {
        $learning_object_type = $request->input('learning_object_type');
        $learning_object_uid = $request->input('learning_object_uid');

        // Puede ser curso o programa formativo
        $this->validateRequest($learning_object_type);

        if ($learning_object_type == "course") {
            $redsys_params = $this->processCoursePayment($learning_object_uid);
        } else if ($learning_object_type == "educational_program") {
            // TODO
        }

        return response()->json($redsys_params, 200);
    }

    private function processCoursePayment($learning_object_uid)
    {
        $course = CoursesModel::where('uid', $learning_object_uid)->with('status')->first();

        $this->validateCourse($course);

        $totalCost = $course->cost;
        $amount = (string)round($totalCost * 100);
        $order_number = generateRandomNumber(12);

        $this->createCoursePayment($course, $order_number);

        return $this->generateRedsysObject($amount, "course", $order_number);
    }

    private function createCoursePayment($course, $order_number)
    {
        $course_payment = new CoursesPaymentsModel();
        $course_payment->uid = generate_uuid();
        $course_payment->user_uid = auth()->user()->uid;
        $course_payment->course_uid = $course->uid;
        $course_payment->order_number = $order_number;
        $course_payment->is_paid = 0;

        $course_payment->save();
    }


    private function generateRedsysObject($amount, $learning_object_type, $order_number)
    {
        // Preparamos el objeto de la API de Redsys
        $miObj = new RedsysAPI;
        $miObj->setParameter("DS_MERCHANT_AMOUNT", $amount);

        $miObj->setParameter("DS_MERCHANT_ORDER", $order_number);
        $miObj->setParameter("DS_MERCHANT_MERCHANTCODE", app('general_options')['redsys_commerce_code']);
        $miObj->setParameter("DS_MERCHANT_CURRENCY", app('general_options')['redsys_currency']);
        $miObj->setParameter("DS_MERCHANT_TRANSACTIONTYPE", app('general_options')['redsys_transaction_type']);
        $miObj->setParameter("DS_MERCHANT_TERMINAL", app('general_options')['redsys_terminal']);
        $miObj->setParameter("DS_MERCHANT_MERCHANTDATA", $learning_object_type);

        $miObj->setParameter("DS_MERCHANT_MERCHANTURL", route('webhook_process_payment_redsys'));

        if ($learning_object_type == "course") $description = "COMPRA DE CURSO";
        else if ($learning_object_type == "educational_program") $description = "COMPRA DE PROGRAMA FORMATIVO";

        $miObj->setParameter("DS_MERCHANT_PRODUCTDESCRIPTION", $description);

        $miObj->setParameter("DS_MERCHANT_URLOK", route('index'));
        $miObj->setParameter("DS_MERCHANT_URLKO", route('error', ['code' => '001']));

        //Datos de configuración
        $version = "HMAC_SHA256_V1";
        $kc = app('general_options')['redsys_encryption_key'];

        // Se generan los parámetros de la petición
        $params = $miObj->createMerchantParameters();
        $signature = $miObj->createMerchantSignature($kc);

        return [
            'Ds_SignatureVersion' => $version,
            'Ds_MerchantParameters' => $params,
            'Ds_Signature' => $signature,
        ];
    }

    private function validateRequest($learning_object_type)
    {
        if (!in_array($learning_object_type, ["course", "educational_program"])) {
            abort(405);
        }
    }

    private function validateCourse($course)
    {
        if (!$course || $course->status->code != "INSCRIPTION") {
            abort(405);
        }
    }
}
