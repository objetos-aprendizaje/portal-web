<?php

namespace App\Http\Controllers\Profile\MyEducationalPrograms;

use App\Exceptions\OperationFailedException;
use App\Models\CoursesAccessesModel;
use App\Models\CoursesModel;
use App\Models\EducationalProgramsPaymentTermsModel;
use App\Models\EducationalProgramsPaymentTermsUsersModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class EnrolledEducationalProgramsController extends BaseController
{
    public function index()
    {
        return view("profile.my_educational_programs.enrolled_educational_programs.index", [
            "resources" => [
                "resources/js/profile/my_educational_programs/enrolled_educational_programs.js"
            ],
            "page_title" => "Mis programas formativos matriculados",
            "currentPage" => "enrolledEducationalPrograms"
        ]);
    }

    public function getEnrolledEducationalPrograms(Request $request)
    {
        $user = auth()->user();
        $items_per_page = $request->items_per_page;
        $search = $request->search;

        $educationalProgramsStudentQuery = $user->educationalPrograms()
            ->with([
                'status',
                'courses',
                'paymentTerms',
                'paymentTerms.userPayment' => function ($query) use ($user) {
                    $query->where('user_uid', $user->uid);
                }
            ])
            ->wherePivot('status', 'ENROLLED')
            ->whereHas('status', function ($query) {
                $query->whereIn('code', ['ENROLLING', 'DEVELOPMENT', 'INSCRIPTION']);
            });

        if ($search) {
            $educationalProgramsStudentQuery->where('name', 'ilike', '%' . $search . '%')->orWhere('description', 'ilike', '%' . $search . '%');
        }

        $educationalProgramsStudent = $educationalProgramsStudentQuery->paginate($items_per_page);

        $educationalProgramsStudent->getCollection()->transform(function ($educationalProgram) {
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
                "payment_mode" => $educationalProgram->payment_mode,
                "courses" => $educationalProgram->courses ? $educationalProgram->courses->map(function ($course) {
                    return [
                        "uid" => $course->uid,
                        "title" => $course->title,
                        "description" => $course->description,
                        "ects_workload" => $course->ects_workload,
                        "lms_url" => $course->lms_url,
                    ];
                }) : null,
                "payment_terms" => $educationalProgram->paymentTerms ? $educationalProgram->paymentTerms->map(function ($paymentTerm) {
                    return [
                        "uid" => $paymentTerm->uid,
                        "name" => $paymentTerm->name,
                        "start_date" => $paymentTerm->start_date,
                        "finish_date" => $paymentTerm->finish_date,
                        "cost" => $paymentTerm->cost,
                        "user_payment" => $paymentTerm->userPayment ? $paymentTerm->userPayment->map(function ($userPayment) {
                            return [
                                "is_paid" => $userPayment->is_paid,
                            ];
                        }) : null
                    ];
                }) : null
            ];
        });
        return response()->json($educationalProgramsStudent);
    }

    public function payTerm(Request $request)
    {
        $paymentTermUid = $request->input("paymentTermUid");

        $paymentTerm = EducationalProgramsPaymentTermsModel::where('uid', $paymentTermUid)->with("educationalProgram")->first();

        if ($paymentTerm->start_date > now() || $paymentTerm->finish_date < now()) {
            throw new OperationFailedException("El plazo de pago no está activo");
        }

        $paymentTermUser = $this->createOrRetrievePaymentTermUser($paymentTermUid);

        $descriptionPaymentTermUser = 'Pago de plazo del programa formativo ' . $paymentTerm->educationalProgram->name;

        $merchantData = json_encode([
            "learningObjectType" => "educationalProgram",
            "paymentType" => "paymentTerm",
        ]);

        $urlOk = route("my-educational-programs-enrolled") . '?payment_success=true';
        $urlKo = route("my-educational-programs-enrolled") . '?payment_success=false';

        $redsysParams = generateRedsysObject($paymentTerm->cost, $paymentTermUser->order_number, $merchantData, $descriptionPaymentTermUser, $urlOk, $urlKo);

        return response()->json([
            "redsysParams" => $redsysParams,
        ]);
    }

    private function createOrRetrievePaymentTermUser($paymentTermUid)
    {
        $paymentTermUser = EducationalProgramsPaymentTermsUsersModel::where('educational_program_payment_term_uid', $paymentTermUid)
            ->where('user_uid', auth()->user()->uid)
            ->first();


        if ($paymentTermUser) {
            $paymentTermUser->order_number = generateRandomNumber(12);
            $paymentTermUser->save();
            return $paymentTermUser;
        }

        $paymentTermUser = EducationalProgramsPaymentTermsUsersModel::create([
            "uid" => generate_uuid(),
            "educational_program_payment_term_uid" => $paymentTermUid,
            "user_uid" => auth()->user()->uid,
            "order_number" => generateRandomNumber(12),
        ]);

        return $paymentTermUser;
    }

    public function accessCourse(Request $request)
    {
        $courseUid = $request->input("courseUid");

        $course = CoursesModel::where('uid', $courseUid)->with(['educationalProgram', 'educationalProgram.status'])->first();

        if ($course->educationalProgram->status->code != 'DEVELOPMENT') {
            throw new OperationFailedException("El programa formativo no se encuentra en desarrollo");
        }

        CoursesAccessesModel::create([
            "uid" => generate_uuid(),
            "course_uid" => $course->uid,
            "user_uid" => auth()->user()->uid,
            "access_date" => now()
        ]);

        return response()->json([
            "lmsUrl" => $course->lms_url
        ]);
    }
}
