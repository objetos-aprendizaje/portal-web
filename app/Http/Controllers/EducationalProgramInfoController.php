<?php

namespace App\Http\Controllers;

use App\Models\CoursesAssessmentsModel;
use App\Models\CoursesModel;
use App\Models\EducationalProgramsAssessmentsModel;
use App\Models\EducationalProgramsModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class EducationalProgramInfoController extends BaseController
{
    public function index($uid)
    {

        $educational_program = $this->getEducationalProgram($uid);

        // Hacemos un array único con los profesores de cada curso
        $teachers = $this->getTeachersUnique($educational_program);

        return view("educational-program-info", [
            "resources" => [
                'resources/js/educational_program_info.js'
            ],
            "educational_program" => $educational_program,
            "teachers" => $teachers,
            'page_title' => $educational_program->name,
        ]);
    }

    private function getTeachersUnique($educational_program) {
        $teachers = [];
        $temp = [];
        foreach ($educational_program->courses as $course) {
            foreach ($course->teachers as $teacher) {
                // Utiliza el uid del teacher como clave en el array temporal.
                $temp[$teacher->uid] = $teacher;
            }
        }
        // Obtén los valores del array temporal para eliminar las claves y tener solo los objetos teacher únicos.
        $teachers = array_values($temp);

        return $teachers;
    }


    public function calificate(Request $request)
    {

        // Validamos que calification sea un número entre 1 y 5
        $request->validate([
            'calification' => 'required|integer|between:1,5',
            'educational_program_uid' => 'required|exists:educational_programs,uid'
        ]);

        $educational_program_uid = $request->input('educational_program_uid');
        $calification = $request->input('calification');

        $calification_value = $request->input('calification');

        $calification = EducationalProgramsAssessmentsModel::where('user_uid', auth()->user()->uid)
            ->where('educational_program_uid', $educational_program_uid)
            ->first();

        if ($calification) {
            $calification->calification = $calification_value;
            $calification->save();
        } else {
            $calification = new EducationalProgramsAssessmentsModel();
            $calification->uid = generate_uuid();
            $calification->user_uid = auth()->user()->uid;
            $calification->educational_program_uid = $educational_program_uid;
            $calification->calification = $calification_value;
            $calification->save();
        }

        return response()->json(['message' => 'Se ha registrado correctamente la calificación'], 200);
    }

    public function getEducationalProgramApi($educational_program_uid)
    {

        $educational_program = $this->getEducationalProgram($educational_program_uid);

        return response()->json($educational_program);
    }


    private function getEducationalProgram($uid)
    {

        $educational_program = EducationalProgramsModel::select('educational_programs.*', 'califications_avg.average_calification')
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
                'educational_program_type'
            ])
            ->addSelect(DB::raw('(SELECT SUM(CAST(ects_workload AS numeric)) FROM courses WHERE courses.educational_program_uid = educational_programs.uid) as total_ects_workload'))
            ->addSelect(['total_cost' => CoursesModel::select(DB::raw('SUM(cost)'))
                ->whereColumn('educational_program_uid', 'educational_programs.uid')
            ])
            ->addSelect('educational_program_statuses.code as status_code')
            ->leftJoin('educational_program_statuses', 'educational_programs.educational_program_status_uid', '=', 'educational_program_statuses.uid')
            ->where('educational_programs.uid', $uid)
            ->first();

        return $educational_program;
    }
}
