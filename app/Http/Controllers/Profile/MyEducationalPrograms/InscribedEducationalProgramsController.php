<?php

namespace App\Http\Controllers\Profile\MyEducationalPrograms;

use App\Exceptions\OperationFailedException;
use App\Models\EducationalProgramsModel;
use App\Models\EducationalProgramsPaymentsModel;
use App\Models\EducationalProgramsStudentsDocumentsModel;
use App\Models\EducationalProgramsStudentsModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InscribedEducationalProgramsController extends BaseController
{

    public function index()
    {
        return view("profile.my_educational_programs.inscribed_educational_programs.index", [
            "resources" => [
                "resources/js/profile/my_educational_programs/inscribed_educational_programs.js"
            ],
            "page_title" => "Mis programas educativos inscritos",
            "currentPage" => "inscribedEducationalPrograms"
        ]);
    }

    public function getInscribedEducationalPrograms(Request $request)
    {
        $user = auth()->user();

        $items_per_page = $request->items_per_page;

        $search = $request->search;

        $coursesStudentsQuery = $user->educationalPrograms()
            ->with([
                'courses',
                'status',
                'educationalProgramDocuments',
                'educationalProgramDocuments.educationalProgramStudentDocument' => function ($query) use ($user) {
                    $query->where('user_uid', $user->uid);
                }
            ])
            ->wherePivot('status', 'INSCRIBED')
            ->whereHas('status', function ($query) {
                $query->whereIn('code', ['INSCRIPTION', 'ENROLLING']);
            });


        if ($search) {
            $coursesStudentsQuery->where('name', 'like', '%' . $search . '%')->orWhere('description', 'like', '%' . $search . '%');
        }

        $coursesStudents = $coursesStudentsQuery->paginate($items_per_page);

        return response()->json($coursesStudents);
    }

    public function enrollEducationalProgram(Request $request)
    {

        $educationalProgramUid = $request->educationalProgramUid;
        $user = auth()->user();

        $educationalProgram = EducationalProgramsModel::where('uid', $educationalProgramUid)->with(['status', 'students'])->first();

        // Comprobamos el estado del curso
        if ($educationalProgram->status->code != 'ENROLLING') {
            throw new OperationFailedException('No puedes matricularte en este curso', 406);
        }

        // Comprobamos que el usuario no estuviera ya matriculado en el curso
        if ($educationalProgram->students()->wherePivot('status', 'ENROLLED')->get()->contains($user->uid)) {
            throw new OperationFailedException('Ya estás matriculado en este curso', 406);
        }

        // Comprobamos si hay requerimiento y en ese caso, comprobamos si el usuario está aprobado
        if ($educationalProgram->students()->wherePivot('acceptance_status', '!=', 'ACCEPTED')->get()->contains($user->uid)) {
            throw new OperationFailedException('No has sido aprobado en este curso', 406);
        }

        // Comprobamos si el curso tiene coste y en ese caso, lo redirigimos a redsys
        if ($educationalProgram->cost && $educationalProgram->cost > 0) {
            $orderNumber = generateRandomNumber(12);
            $this->createEducationalProgramPayment($educationalProgram->uid, $orderNumber);
            $redsysParams = generateRedsysObject($educationalProgram->cost, $orderNumber,  "educational_program", "Compra de programa formativo");

            $response = [
                "requirePayment" => true,
                "redsysParams" => $redsysParams
            ];
        } else {
            // Buscamos en students el usuario y lo marcamos como matriculado
            $educationalProgram->students()->updateExistingPivot($user->uid, [
                'status' => 'ENROLLED',
            ]);

            $response = [
                "requirePayment" => false,
                "message" => "Matriculado en el curso correctamente"
            ];
        }

        return response()->json($response);
    }

    private function createEducationalProgramPayment($educationalProgramUid, $orderNumber)
    {
        $course_payment = new EducationalProgramsPaymentsModel();
        $course_payment->uid = generate_uuid();
        $course_payment->user_uid = auth()->user()->uid;
        $course_payment->educational_program_uid = $educationalProgramUid;
        $course_payment->order_number = $orderNumber;
        $course_payment->is_paid = 0;

        $course_payment->save();
    }

    public function saveDocumentsEducationalProgram(Request $request)
    {
        DB::transaction(function () use ($request) {
            $files = $request->allFiles();

            foreach ($files as $documentUid => $document) {
                // Guardamos el fichero
                $filePath = $this->uploadUserFileBackend($document);
                $this->saveDocumentEducationalProgramStudent($documentUid, $filePath);
            }
        });

        return response()->json(['message' => 'Documentos guardados correctamente', 200]);
    }

    // Enviar la imagen al webhook del backend y nos devuelve la ruta asignada
    public function uploadUserFileBackend($file)
    {
        $header = ['API-KEY: ' . env('API_KEY_BACKEND_WEBHOOKS')];

        $url = env('BACKEND_URL') . '/api/upload_file';

        $response = sendFileToBackend($file, $url, $header);

        return $response['file_path'];
    }

    private function saveDocumentEducationalProgramStudent($documentUid, $filePath)
    {
        $documentCourseStudent = EducationalProgramsStudentsDocumentsModel::where([
            'educational_program_document_uid' => $documentUid,
            'user_uid' => auth()->user()->uid
        ])->first();

        if (!$documentCourseStudent) {
            $documentCourseStudent = new EducationalProgramsStudentsDocumentsModel();
            $documentCourseStudent->uid = generate_uuid();
            $documentCourseStudent->educational_program_document_uid = $documentUid;
            $documentCourseStudent->user_uid = auth()->user()->uid;
        }

        $documentCourseStudent->document_path = $filePath;

        $documentCourseStudent->save();
    }

    public function downloadDocumentEducationalProgram(Request $request)
    {
        $documentEducationalProgramUid = $request->input('educational_program_document_uid');

        $documentCourse = EducationalProgramsStudentsDocumentsModel::where('uid', $documentEducationalProgramUid)->firstOrFail();

        if (!$documentCourse) {
            return response()->json(['message' => 'No se ha encontrado el documento'], 404);
        }

        $documentPath = $documentCourse->document_path;

        $header = ['API-KEY: ' . env('API_KEY_BACKEND_WEBHOOKS')];

        $url = env('BACKEND_URL') . '/api/download_file';

        $postFields = ['file_path' => $documentPath];

        $downloadPath = storage_path('/app/files_downloaded_temp/') . basename($documentPath);

        downloadFileFromBackend($url, $postFields, $downloadPath, $header);

        return response()->download($downloadPath);
    }
}
