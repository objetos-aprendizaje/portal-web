<?php

namespace App\Http\Controllers\Profile\MyCourses;

use App\Exceptions\OperationFailedException;
use App\Models\CoursesAccessesModel;
use App\Models\CoursesModel;
use App\Models\CoursesPaymentTermsModel;
use App\Models\CoursesPaymentTermsUsersModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class EnrolledCoursesController extends BaseController
{
    public function index()
    {
        return view("profile.my_courses.enrolled_courses.index", [
            "resources" => [
                "resources/js/profile/my_courses/enrolled_courses.js"
            ],
            "page_title" => "Mis cursos matriculados",
            "currentPage" => "enrolledCourses"
        ]);
    }

    public function getEnrolledCourses(Request $request)
    {
        $user = auth()->user();
        $items_per_page = $request->items_per_page;
        $search = $request->search;

        $coursesStudentQuery = $user->courses_students()
            ->with([
                'status',
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
            $coursesStudentQuery->where('title', 'like', '%' . $search . '%')->orWhere('description', 'like', '%' . $search . '%');
        }

        $coursesStudent = $coursesStudentQuery->paginate($items_per_page);

        return response()->json($coursesStudent);
    }

    public function payTerm(Request $request)
    {
        $paymentTermUid = $request->input("paymentTermUid");

        $paymentTerm = CoursesPaymentTermsModel::where('uid', $paymentTermUid)->with("course")->first();

        if ($paymentTerm->start_date > now() || $paymentTerm->finish_date < now()) {
            throw new OperationFailedException("El plazo de pago no está activo");
        }

        $paymentTermUser = $this->createOrRetrievePaymentTermUser($paymentTermUid);

        $descriptionPaymentTermUser = 'Pago de plazo del curso ' . $paymentTerm->course->title;

        $merchantData = json_encode([
            "learningObjectType" => "course",
            "paymentType" => "paymentTerm",
        ]);

        $urlOk = route("my-courses-enrolled") . '?payment_success=true';
        $urlKo = route("my-courses-enrolled") . '?payment_success=false';

        $redsysParams = generateRedsysObject($paymentTerm->cost, $paymentTermUser->order_number, $merchantData, $descriptionPaymentTermUser, $urlOk, $urlKo);

        return response()->json([
            "redsysParams" => $redsysParams,
        ]);
    }

    private function createOrRetrievePaymentTermUser($paymentTermUid)
    {
        $paymentTermUser = CoursesPaymentTermsUsersModel::where('course_payment_term_uid', $paymentTermUid)
            ->where('user_uid', auth()->user()->uid)
            ->first();

        if ($paymentTermUser) {
            $paymentTermUser->order_number = generateRandomNumber(12);
            $paymentTermUser->save();
            return $paymentTermUser;
        }

        $paymentTermUser = CoursesPaymentTermsUsersModel::create([
            "uid" => generate_uuid(),
            "course_payment_term_uid" => $paymentTermUid,
            "user_uid" => auth()->user()->uid,
            "order_number" => generateRandomNumber(12),
        ]);

        return $paymentTermUser;
    }


    public function accessCourse(Request $request)
    {
        $courseUid = $request->input("courseUid");

        $course = CoursesModel::where('uid', $courseUid)->with('status')->first();

        if ($course->status->code != 'DEVELOPMENT') {
            abort(403);
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
