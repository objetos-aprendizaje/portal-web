<?php

namespace App\Http\Controllers;

use App\Models\CoursesAssessmentsModel;
use App\Models\CoursesModel;
use App\Models\CoursesVisitsModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseInfoController extends BaseController
{
    public function index($uid)
    {
        $course = $this->getCourse($uid);

        adaptDatesCourseEducationalProgram($course);

        // Todos los resultados de aprendizaje
        $learningResults = [];
        foreach($course->blocks as $block) {
            foreach($block->learningResults as $learningResult) {
                $learningResults[] = $learningResult;
            }
        }

        // Grabar la visita
        CoursesVisitsModel::insert([
            'uid' => generate_uuid(),
            'course_uid' => $course->uid,
            'user_uid' => Auth::user() ? Auth::user()->uid : null,
            'access_date' => now()
        ]);

        $showDoubtsButton = $course->contact_emails->count() || $course->educational_program_type->redirection_queries->count();
        return view("course-info", [
            'course' => $course,
            'learningResults' => $learningResults,
            'showDoubtsButton' => $showDoubtsButton,
            "resources" => [
                'resources/js/course_info.js'
            ],
            'page_title' => $course->title,
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

    private function getCourse($uid) {
        $course = CoursesModel::select('courses.*', 'califications_avg.average_calification')->where('uid', $uid)->with([
            'blocks.learningResults', 'status', 'tags', 'teachers', 'course_type', 'paymentTerms', 'contact_emails', 'educational_program_type.redirection_queries'
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

        return $course;
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
