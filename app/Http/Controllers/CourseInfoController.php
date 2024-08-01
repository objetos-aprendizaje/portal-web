<?php

namespace App\Http\Controllers;

use App\Models\CoursesAssessmentsModel;
use App\Models\CoursesModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseInfoController extends BaseController
{
    public function index($uid)
    {
        $course = CoursesModel::select('courses.*', 'califications_avg.average_calification')->where('uid', $uid)->with([
            'status', 'tags', 'teachers', 'course_type', 'paymentTerms',
            'blocks.competences' => function ($query) {
                $query->where('is_multi_select', 0);
            }

        ])
            ->leftJoinSub(
                CoursesAssessmentsModel::select('course_uid', DB::raw('ROUND(AVG(calification), 1) as average_calification'))
                    ->groupBy('course_uid'),
                'califications_avg', // Alias de la subconsulta
                'califications_avg.course_uid',
                '=',
                'courses.uid'
            )
            ->first();

        if (!$course) abort(404);

        // Extraemos un array con las competencias
        $competences = [];
        foreach ($course->blocks as $block) {
            foreach ($block->competences as $competence) {
                $competences[] = $competence;
            }
        }

        return view("course-info", [
            'course' => $course,
            'competences' => $competences,
            "resources" => [
                'resources/js/course_info.js'
            ],
            'page_title' => $course->title . ' | POA',
        ]);
    }

    public function getCourseCalification(Request $request)
    {
        $course_uid = $request->input('course_uid');
        $calification = CoursesAssessmentsModel::where('course_uid', $course_uid)
            ->where('user_uid', Auth::user()->uid)
            ->first();

        $calification_number = $calification->calification = number_format($calification->calification, 1);

        return response()->json([
            'calification' => $calification_number
        ]);
    }


    public function calificate(Request $request)
    {
        $stars = $request->input('stars');

        CoursesAssessmentsModel::updateOrCreate(
            [
                'course_uid' => $request->input('course_uid'),
                'user_uid' => Auth::user()->uid,
            ],
            [
                'uid' => generate_uuid(),
                'calification' => $stars
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Calificación realizada con éxito'
        ]);
    }
}
