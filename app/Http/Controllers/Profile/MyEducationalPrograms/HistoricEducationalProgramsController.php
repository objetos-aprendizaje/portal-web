<?php

namespace App\Http\Controllers\Profile\MyEducationalPrograms;

use App\Exceptions\OperationFailedException;
use App\Models\CoursesAccessesModel;
use App\Models\CoursesModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class HistoricEducationalProgramsController extends BaseController
{
    public function index()
    {
        return view("profile.my_educational_programs.historic_educational_programs.index", [
            "resources" => [
                "resources/js/profile/my_educational_programs/historic_educational_programs.js"
            ],
            "page_title" => "Histórico de programas formativos",
            "currentPage" => "historicEducationalPrograms"
        ]);
    }

    public function getHistoricEducationalPrograms(Request $request)
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
                $query->whereIn('code', ['FINISHED']);
            });

        if ($search) {
            $educationalProgramsStudentQuery->where(function ($query) use ($search) {
                $query->where('name', 'ilike', '%' . $search . '%')
                    ->orWhere('description', 'ilike', '%' . $search . '%');
            });
        }

        $educationalProgramsStudent = $educationalProgramsStudentQuery->paginate($items_per_page);

        $educationalProgramsStudent->getCollection()->transform(function ($educationalProgram) {
            return [
                "uid" => $educationalProgram->uid,
                "name" => $educationalProgram->name,
                "image_path" => $educationalProgram->image_path,
                "realization_start_date" => adaptDateTimezoneDisplay($educationalProgram->realization_start_date),
                "realization_finish_date" => adaptDateTimezoneDisplay($educationalProgram->realization_finish_date),
                "lms_url" => $educationalProgram->lms_url,
                "courses" => $educationalProgram->courses ? $educationalProgram->courses->map(function ($course) {
                    return [
                        "uid" => $course->uid,
                        "title" => $course->title,
                        "description" => $course->description,
                        "ects_workload" => $course->ects_workload,
                        "lms_url" => $course->lms_url,
                    ];
                }) : null,
            ];
        });

        return response()->json($educationalProgramsStudent);
    }

    public function accessCourse(Request $request)
    {
        $courseUid = $request->input("courseUid");

        $course = CoursesModel::where('uid', $courseUid)->with(['educationalProgram', 'educationalProgram.status'])->first();

        if($course->educationalProgram->status->code != 'FINISHED') {
            throw new OperationFailedException("El programa formativo no se encuentra finalizado");
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
