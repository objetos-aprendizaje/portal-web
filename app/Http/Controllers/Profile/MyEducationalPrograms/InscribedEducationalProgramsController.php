<?php

namespace App\Http\Controllers\Profile\MyEducationalPrograms;

use App\Exceptions\OperationFailedException;
use App\Models\BackendFileDownloadTokensModel;
use App\Models\EducationalProgramsModel;
use App\Models\EducationalProgramsPaymentsModel;
use App\Models\EducationalProgramsStudentsDocumentsModel;
use App\Models\EducationalProgramsStudentsModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InscribedEducationalProgramsController extends BaseController
{

    public function index()
    {
        return view("profile.my_educational_programs.inscribed_educational_programs.index", [
            "resources" => [
                "resources/js/profile/my_educational_programs/inscribed_educational_programs.js"
            ],
            "page_title" => "Mis programas formativos inscritos",
            "currentPage" => "inscribedEducationalPrograms"
        ]);
    }

    public function getInscribedEducationalPrograms(Request $request)
    {
        $user = auth()->user();

        $items_per_page = $request->items_per_page;

        $search = $request->search;

        $educationalProgramsStudentQuery = $user->educationalPrograms()
            ->with([
                'courses',
                'status',
                'educationalProgramDocuments',
                'paymentTerms',
                'educationalProgramDocuments.educationalProgramStudentDocument' => function ($query) use ($user) {
                    $query->where('user_uid', $user->uid);
                }
            ])
            ->wherePivot('status', 'INSCRIBED')
            ->whereHas('status', function ($query) {
                $query->whereIn('code', ['INSCRIPTION', 'ENROLLING']);
            });

        if ($search) {
            $educationalProgramsStudentQuery->where(function ($query) use ($search) {
                $query->where('name', 'ilike', '%' . $search . '%')
                    ->orWhere('description', 'ilike', '%' . $search . '%');
            });
        }

        $educationalProgramsStudents = $educationalProgramsStudentQuery->paginate($items_per_page);

        $educationalProgramsStudents->getCollection()->transform(function ($educationalProgram) {
            return [
                "uid" => $educationalProgram->uid,
                "name" => $educationalProgram->name,
                "description" => $educationalProgram->description,
                "enrolling_start_date" => adaptDateTimezoneDisplay($educationalProgram->enrolling_start_date),
                "enrolling_finish_date" => adaptDateTimezoneDisplay($educationalProgram->enrolling_finish_date),
                "realization_start_date" => adaptDateTimezoneDisplay($educationalProgram->realization_start_date),
                "realization_finish_date" => adaptDateTimezoneDisplay($educationalProgram->realization_finish_date),
                "cost" => $educationalProgram->cost,
                "status_code" => $educationalProgram->status->code,
                "acceptance_status" => $educationalProgram->pivot->acceptance_status,
                "validate_student_registrations" => $educationalProgram->validate_student_registrations,
                "image_path" => $educationalProgram->image_path,
                "evaluation_criteria" => $educationalProgram->evaluation_criteria,
                "courses" => $educationalProgram->courses ? $educationalProgram->courses->map(function ($course) {
                    return [
                        "uid" => $course->uid,
                        "title" => $course->title,
                        "description" => $course->description,
                        "ects_workload" => $course->ects_workload,

                    ];
                }) : null,
                "educational_program_documents" => $educationalProgram->educationalProgramDocuments ? $educationalProgram->educationalProgramDocuments->map(function ($educationalProgramDocument) {
                    $educationalProgramDocumentsMapped = [
                        "uid" => $educationalProgramDocument->uid,
                        "document_name" => $educationalProgramDocument->document_name,
                    ];

                    if ($educationalProgramDocument->educationalProgramStudentDocument) {
                        $educationalProgramDocumentsMapped['educational_program_student_document'] = [
                            "uid" => $educationalProgramDocument->educationalProgramStudentDocument->uid,
                            "document_path" => $educationalProgramDocument->educationalProgramStudentDocument->document_path
                        ];
                    }

                    return $educationalProgramDocumentsMapped;
                }) : null,
                "payment_mode" => $educationalProgram->payment_mode,
            ];
        });

        return response()->json($educationalProgramsStudents);
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

            $merchantData = json_encode([
                "learningObjectType" => "educationalProgram",
                "paymentType" => "singlePayment",
            ]);

            $urlOk = route("my-educational-programs-enrolled") . '?payment_success=true';
            $urlKo = route("my-educational-programs-inscribed") . '?payment_success=false';

            $descriptionPaymentTermUser = 'Pago de plazo del curso ' . $educationalProgram->name;

            $educationalProgramPayment = $this->createOrRetrieveEducationalProgramPayment($educationalProgram->uid);
            $redsysParams = generateRedsysObject($educationalProgram->cost, $educationalProgramPayment->order_number,  $merchantData, $descriptionPaymentTermUser, $urlOk, $urlKo);

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

    private function createOrRetrieveEducationalProgramPayment($educationalProgramUid)
    {
        $educationalProgramPayment = EducationalProgramsPaymentsModel::where([
            'user_uid' => auth()->user()->uid,
            'educational_program_uid' => $educationalProgramUid
        ])->first();

        if ($educationalProgramPayment) {
            $educationalProgramPayment->order_number = generateRandomNumber(12);
            $educationalProgramPayment->save();
            return $educationalProgramPayment;
        }

        $educationalProgramPayment = new EducationalProgramsPaymentsModel();
        $educationalProgramPayment->uid = generate_uuid();
        $educationalProgramPayment->user_uid = auth()->user()->uid;
        $educationalProgramPayment->educational_program_uid = $educationalProgramUid;
        $educationalProgramPayment->order_number = generateRandomNumber(12);
        $educationalProgramPayment->is_paid = 0;

        $educationalProgramPayment->save();

        return $educationalProgramPayment;
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

        $documentEducationalProgram = EducationalProgramsStudentsDocumentsModel::where('uid', $documentEducationalProgramUid)->firstOrFail();

        if (!$documentEducationalProgram) {
            return response()->json(['message' => 'No se ha encontrado el documento'], 404);
        }

        $documentPath = $documentEducationalProgram->document_path;

        $backendFileDownloadToken = new BackendFileDownloadTokensModel();
        $backendFileDownloadToken->uid = generate_uuid();
        $backendFileDownloadToken->token = generateToken(255);
        $backendFileDownloadToken->file = $documentPath;
        $backendFileDownloadToken->save();

        return response()->json(['token' => $backendFileDownloadToken->token]);
    }

    public function cancelInscription(Request $request)
    {
        $educationalProgramUid = $request->educationalProgramUid;
        $user = auth()->user();

        $educationalProgramStudent = EducationalProgramsStudentsModel::where([
            'user_uid' => $user->uid,
            'educational_program_uid' => $educationalProgramUid,
            'status' => 'INSCRIBED'
        ])->first();

        if (!$educationalProgramStudent) {
            throw new OperationFailedException('No estás inscrito en este programa formativo', 406);
        }

        $educationalProgramStudent->delete();

        return response()->json(['message' => 'Inscripción cancelada correctamente']);
    }
}
