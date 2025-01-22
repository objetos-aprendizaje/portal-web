<?php

namespace App\Http\Controllers;

use App\Models\CoursesAssessmentsModel;
use App\Models\CoursesModel;
use App\Models\EducationalProgramsAssessmentsModel;
use App\Models\EducationalProgramsModel;
use App\Models\EducationalProgramsStudentsModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class EducationalProgramInfoController extends BaseController
{
    public function index($uid)
    {

        $educationalProgram = $this->getEducationalProgram($uid);

        if(Auth::check()) {
            $studentEducationalProgramInfo = EducationalProgramsStudentsModel::where('educational_program_uid', $educationalProgram->uid)->where('user_uid', Auth::user()->uid)->first();
        } else {
            $studentEducationalProgramInfo = null;
        }

        adaptDatesCourseEducationalProgram($educationalProgram);

        // Hacemos un array único con los profesores de cada curso
        $teachers = $this->getTeachersUnique($educationalProgram);

        $showDoubtsButton = $educationalProgram->contact_emails->count() || $educationalProgram->educational_program_type->redirection_queries->count();

        return view("educational-program-info", [
            "resources" => [
                'resources/js/educational_program_info.js'
            ],
            'showDoubtsButton' => $showDoubtsButton,
            "educational_program" => $educationalProgram,
            "teachers" => $teachers,
            'page_title' => $educationalProgram->name,
            'studentEducationalProgramInfo' => $studentEducationalProgramInfo
        ]);
    }

    private function getTeachersUnique($educationalProgram) {
        $teachers = [];
        $temp = [];
        foreach ($educationalProgram->courses as $course) {
            foreach ($course->teachers as $teacher) {
                // Utiliza el uid del teacher como clave en el array temporal.
                $temp[$teacher->uid] = $teacher;
            }
        }
        // Obtén los valores del array temporal para eliminar las claves y tener solo los objetos teacher únicos.
        return array_values($temp);
    }


    public function calificate(Request $request)
    {

        // Validamos que calification sea un número entre 1 y 5
        $request->validate([
            'calification' => 'required|integer|between:1,5',
            'educational_program_uid' => 'required|exists:educational_programs,uid'
        ]);

        $educationalProgramUid = $request->input('educational_program_uid');

        $calificationValue = $request->input('calification');

        $calification = EducationalProgramsAssessmentsModel::where('user_uid', auth()->user()->uid)
            ->where('educational_program_uid', $educationalProgramUid)
            ->first();

        if ($calification) {
            $calification->calification = $calificationValue;
            $calification->save();
        } else {
            $calification = new EducationalProgramsAssessmentsModel();
            $calification->uid = generate_uuid();
            $calification->user_uid = auth()->user()->uid;
            $calification->educational_program_uid = $educationalProgramUid;
            $calification->calification = $calificationValue;
            $calification->save();
        }

        return response()->json(['message' => 'Se ha registrado correctamente la calificación'], 200);
    }

    public function getEducationalProgramApi($educationalProgramUid)
    {

        $educationalProgram = $this->getEducationalProgram($educationalProgramUid);

        return response()->json($educationalProgram);
    }


    private function getEducationalProgram($uid)
    {

        $educationalProgram = EducationalProgramsModel::select('educational_programs.*', 'califications_avg.average_calification')
            ->leftJoinSub(
                EducationalProgramsAssessmentsModel::select('educational_program_uid', DB::raw('ROUND(AVG(calification), 1) as average_calification'))
                    ->groupBy('educational_program_uid'),
                'califications_avg', // Alias de la subconsulta
                'califications_avg.educational_program_uid',
                '=',
                'educational_programs.uid'
            )
            ->with([
                'courses' => function ($query) {
                    $query->select('courses.*', 'califications_avg.average_calification')
                        ->leftJoinSub(
                            CoursesAssessmentsModel::select('course_uid', DB::raw('ROUND(AVG(calification), 1) as average_calification'))
                                ->groupBy('course_uid'),
                            'califications_avg', // Alias de la subconsulta
                            'califications_avg.course_uid',
                            '=',
                            'courses.uid'
                        );
                },
                'courses.teachers',
                'educational_program_type',
                'contact_emails',
                'educational_program_type.redirection_queries'
            ])
            ->addSelect(DB::raw('(SELECT SUM(CAST(ects_workload AS numeric)) FROM courses WHERE courses.educational_program_uid = educational_programs.uid) as total_ects_workload'))
            ->addSelect(['total_cost' => CoursesModel::select(DB::raw('SUM(cost)'))
                ->whereColumn('educational_program_uid', 'educational_programs.uid')
            ])
            ->addSelect('educational_program_statuses.code as status_code')
            ->leftJoin('educational_program_statuses', 'educational_programs.educational_program_status_uid', '=', 'educational_program_statuses.uid')
            ->where('educational_programs.uid', $uid)
            ->with(["categories", "center", "status", "educational_program_type"])
            ->first();

        return $educationalProgram;
    }
}
