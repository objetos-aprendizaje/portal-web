<?php

namespace App\Http\Controllers\Profile\MyCourses;

use App\Models\CoursesAccessesModel;
use App\Models\CoursesModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class HistoricCoursesController extends BaseController
{
    public function index()
    {
        return view("profile.my_courses.historic_courses.index", [
            "resources" => [
                "resources/js/profile/my_courses/historic_courses.js"
            ],
            "page_title" => "Histórico de cursos",
            "currentPage" => "historicCourses"
        ]);
    }

    public function getHistoricCourses(Request $request)
    {
        $user = auth()->user();
        $items_per_page = $request->items_per_page;
        $search = $request->search;

        $coursesStudentQuery = $user->courses_students()
            ->with([
                'status',
            ])
            ->wherePivot('status', 'ENROLLED')
            ->whereHas('status', function ($query) {
                $query->where('code', 'FINISHED');
            });

        if ($search) {
            $coursesStudentQuery->where('title', 'like', '%' . $search . '%')->orWhere('description', 'like', '%' . $search . '%');
        }

        $coursesStudent = $coursesStudentQuery->paginate($items_per_page);

        return response()->json($coursesStudent);
    }

    public function accessCourse(Request $request)
    {
        $courseUid = $request->input("courseUid");

        $course = CoursesModel::where('uid', $courseUid)->with('status')->first();

        if ($course->status->code != 'FINISHED') {
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
