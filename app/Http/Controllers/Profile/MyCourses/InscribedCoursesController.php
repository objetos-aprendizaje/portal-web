<?php

namespace App\Http\Controllers\Profile\MyCourses;

use App\Exceptions\OperationFailedException;
use App\Models\CoursesModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\BackendFileDownloadTokensModel;
use App\Models\CoursesPaymentsModel;
use App\Models\CoursesStudentsDocumentsModel;
use App\Models\CoursesStudentsModel;
use Illuminate\Support\Facades\DB;

class InscribedCoursesController extends BaseController
{

    public function index()
    {
        return view("profile.my_courses.inscribed_courses.index", [
            "resources" => [
                "resources/js/profile/my_courses/inscribed_courses.js"
            ],
            "page_title" => "Mis cursos inscritos",
            "currentPage" => "inscribedCourses"
        ]);
    }

    public function getInscribedCourses(Request $request)
    {
        $user = auth()->user();

        $items_per_page = $request->items_per_page;

        $search = $request->search;

        $coursesStudentsQuery = $user->courses_students()
            ->with([
                'status',
                'course_documents',
                'course_documents.course_student_document' => function ($query) use ($user) {
                    $query->where('user_uid', $user->uid);
                },
                'paymentTerms',
                'paymentTerms.userPayment' => function ($query) use ($user) {
                    $query->where('user_uid', $user->uid);
                }
            ])
            ->wherePivot('status', 'INSCRIBED')
            ->whereHas('status', function ($query) {
                $query->whereIn('code', ['INSCRIPTION', 'ENROLLING']);
            });

        if ($search) {
            $coursesStudentsQuery->where('title', 'like', '%' . $search . '%')->orWhere('description', 'like', '%' . $search . '%');
        }

        $coursesStudents = $coursesStudentsQuery->paginate($items_per_page);

        return response()->json($coursesStudents);
    }

    public function enrollCourse(Request $request)
    {
        $courseUid = $request->course_uid;
        $user = auth()->user();

        $course = CoursesModel::where('uid', $courseUid)->with(['status', 'students'])->first();

        // Comprobamos el estado del curso
        if ($course->status->code != 'ENROLLING') {
            throw new OperationFailedException('No puedes matricularte en este curso', 406);
        }

        // Comprobamos que el usuario no estuviera ya matriculado en el curso
        if ($course->students()->wherePivot('status', 'ENROLLED')->get()->contains($user->uid)) {
            throw new OperationFailedException('Ya estás matriculado en este curso', 406);
        }

        // Comprobamos si hay requerimiento y en ese caso, comprobamos si el usuario está aprobado
        if ($course->students()->wherePivot('acceptance_status', '!=', 'ACCEPTED')->get()->contains($user->uid)) {
            throw new OperationFailedException('No has sido aprobado en este curso', 406);
        }

        // Comprobamos si el curso tiene coste y en ese caso, lo redirigimos a redsys
        if ($course->cost && $course->cost > 0) {
            $coursePayment = $this->createOrRetrieveCoursePayment($course->uid);

            $merchantData = json_encode([
                "learningObjectType" => "course",
                "paymentType" => "singlePayment",
            ]);

            $urlOk = route("my-courses-enrolled") . '?payment_success=true';
            $urlKo = route("my-courses-inscribed") . '?payment_success=false';

            $descriptionPaymentTermUser = 'Pago de plazo del curso ' . $course->title;

            $redsysParams = generateRedsysObject($course->cost, $coursePayment->order_number, $merchantData, $descriptionPaymentTermUser, $urlOk, $urlKo);

            $response = [
                "requirePayment" => true,
                "redsysParams" => $redsysParams
            ];
        } else {
            // Buscamos en students el usuario y lo marcamos como matriculado
            $course->students()->updateExistingPivot($user->uid, [
                'status' => 'ENROLLED',
            ]);

            $response = [
                "requirePayment" => false,
                "message" => "Matriculado en el curso correctamente"
            ];
        }

        return response()->json($response);
    }

    private function createOrRetrieveCoursePayment($courseUid)
    {
        $coursePayment = CoursesPaymentsModel::where('course_uid', $courseUid)
            ->where('user_uid', auth()->user()->uid)
            ->first();

        if ($coursePayment) {
            $coursePayment->order_number = generateRandomNumber(12);
            $coursePayment->save();
            return $coursePayment;
        }

        $course_payment = new CoursesPaymentsModel();
        $course_payment->uid = generate_uuid();
        $course_payment->user_uid = auth()->user()->uid;
        $course_payment->course_uid = $courseUid;
        $course_payment->order_number = generateRandomNumber(12);
        $course_payment->is_paid = 0;

        $course_payment->save();

        return $course_payment;
    }

    // Enviar la imagen al webhook del backend y nos devuelve la ruta asignada
    public function uploadUserFileBackend($file)
    {
        $header = ['API-KEY: ' . env('API_KEY_BACKEND_WEBHOOKS')];

        $url = env('BACKEND_URL') . '/api/upload_file';

        $response = sendFileToBackend($file, $url, $header);

        return $response['file_path'];
    }

    public function saveDocumentsCourse(Request $request)
    {
        DB::transaction(function () use ($request) {
            $files = $request->allFiles();

            foreach ($files as $documentUid => $document) {
                // Guardamos el fichero
                $filePath = $this->uploadUserFileBackend($document);
                $this->saveDocumentCourseStudent($documentUid, $filePath);
            }
        });

        return response()->json(['message' => 'Documentos guardados correctamente', 200]);
    }

    private function saveDocumentCourseStudent($documentUid, $filePath)
    {
        $documentCourseStudent = CoursesStudentsDocumentsModel::where([
            'course_document_uid' => $documentUid,
            'user_uid' => auth()->user()->uid
        ])->first();

        if (!$documentCourseStudent) {
            $documentCourseStudent = new CoursesStudentsDocumentsModel();
            $documentCourseStudent->uid = generate_uuid();
            $documentCourseStudent->course_document_uid = $documentUid;
            $documentCourseStudent->user_uid = auth()->user()->uid;
        }

        $documentCourseStudent->document_path = $filePath;

        $documentCourseStudent->save();
    }

    public function downloadDocumentCourse(Request $request)
    {
        $documentCourseUid = $request->input('course_document_uid');

        $documentCourse = CoursesStudentsDocumentsModel::where('uid', $documentCourseUid)->firstOrFail();

        if (!$documentCourse) {
            throw new OperationFailedException('No se ha encontrado el documento', 404);
        }

        $documentPath = $documentCourse->document_path;

        // Este token será el que se enviará al backend para que ofrezca la descarga del fichero
        // Una vez iniciada la descarga, el token se elimina
        $backendFileDownloadToken = new BackendFileDownloadTokensModel();
        $backendFileDownloadToken->uid = generate_uuid();
        $backendFileDownloadToken->token = generateToken(255);
        $backendFileDownloadToken->file = $documentPath;

        $backendFileDownloadToken->save();

        return response()->json(['token' => $backendFileDownloadToken->token]);
    }

    public function cancelInscription(Request $request)
    {
        $courseUid = $request->course_uid;

        $courseStudent = CoursesStudentsModel::where('course_uid', $courseUid)
            ->where('user_uid', auth()->user()->uid)
            ->where('status', 'INSCRIBED')
            ->first();

        if (!$courseStudent) {
            throw new OperationFailedException('No estás inscrito en este curso', 406);
        }

        $courseStudent->delete();

        return response()->json(['message' => 'Inscripción cancelada correctamente']);
    }
}
