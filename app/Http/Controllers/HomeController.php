<?php

namespace App\Http\Controllers;

use App\Models\CoursesAssessmentsModel;
use App\Models\CoursesModel;
use App\Models\EducationalResourcesAssessmentsModel;
use App\Models\EducationalResourcesModel;
use App\Models\GeneralOptionsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $general_options = GeneralOptionsModel::all()->pluck('option_value', 'option_name')->toArray();

        $featured_courses = CoursesModel::select('courses.*', 'califications_avg.average_calification')
            ->leftJoinSub(
                CoursesAssessmentsModel::select('course_uid', DB::raw('ROUND(AVG(calification), 1) as average_calification'))
                    ->groupBy('course_uid'),
                'califications_avg', // Alias de la subconsulta
                'califications_avg.course_uid',
                '=',
                'courses.uid'
            )->with('status')
            ->whereHas('status', function ($query) {
                $query->whereIn('code', ['INSCRIPTION', 'ACCEPTED_PUBLICATION']);
            })
            ->orderBy('califications_avg.average_calification', 'desc')
            ->get()->toArray();

            $educational_resources = EducationalResourcesModel::with('status')->select('educational_resources.*', 'califications_avg.average_calification')->whereHas('status', function ($query) {
                $query->where('code', 'PUBLISHED');
            })
                ->leftJoinSub(
                    EducationalResourcesAssessmentsModel::select('educational_resources_uid', DB::raw('ROUND(AVG(calification), 1) as average_calification'))
                        ->groupBy('educational_resources_uid'),
                    'califications_avg', // Alias de la subconsulta
                    'califications_avg.educational_resources_uid',
                    '=',
                    'educational_resources.uid' // Corregido aquí
                )
                ->get()->toArray();

        return view("home", [
            'resources' => ["resources/js/home.js", "resources/js/carrousel.js"],
            'featured_courses' => $featured_courses,
            'general_options' => $general_options,
            'educational_resources' => $educational_resources,
        ]);
    }
}
