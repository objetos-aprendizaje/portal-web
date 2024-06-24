<?php

namespace App\Http\Controllers\Profile\MyEducationalPrograms;

use App\Exceptions\OperationFailedException;
use App\Models\CoursesAccessesModel;
use App\Models\CoursesModel;
use App\Models\EducationalProgramsAccessesModel;
use App\Models\EducationalProgramsModel;
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
                'courses'
            ])
            ->wherePivot('status', 'ENROLLED')
            ->whereHas('status', function ($query) {
                $query->whereIn('code', ['ENROLLING', 'DEVELOPMENT', 'INSCRIPTION']);
            });

        if ($search) {
            $educationalProgramsStudentQuery->where('name', 'like', '%' . $search . '%')->orWhere('description', 'like', '%' . $search . '%');
        }

        $educationalProgramsStudent = $educationalProgramsStudentQuery->paginate($items_per_page);

        return response()->json($educationalProgramsStudent);
    }

    public function accessCourse(Request $request)
    {
        $courseUid = $request->input("courseUid");

        $course = CoursesModel::where('uid', $courseUid)->with(['educationalProgram', 'educationalProgram.status'])->first();

        if($course->educationalProgram->status->code != 'DEVELOPMENT') {
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
