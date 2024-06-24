<?php

namespace App\Http\Controllers;

use App\Models\CategoriesModel;
use App\Models\CoursesAssessmentsModel;
use App\Models\CoursesBigCarrouselsApprovalsModel;
use App\Models\CoursesModel;
use App\Models\CoursesSmallCarrouselsApprovalsModel;
use App\Models\EducationalProgramsAssessmentsModel;
use App\Models\EducationalProgramsModel;
use App\Models\EducationalResourcesAssessmentsModel;
use App\Models\EducationalResourcesModel;
use App\Models\GeneralOptionsModel;
use App\Models\SlidersPrevisualizationsModel;
use App\Models\UserLanesModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class HomeController extends BaseController
{
    public function index()
    {
        $general_options = GeneralOptionsModel::all()->pluck('option_value', 'option_name')->toArray();

        $featured_courses = $this->getFeaturedCourses();

        $educational_resources = $this->getEducationalResources();

        $categories = $this->getCategories();

        $lanes_preferences = $this->getLanesPreferences();

        $filtered_courses_carrousel = $this->filterFeaturedCoursesCarrousel($featured_courses);

        $featuredEducationalPrograms = $this->getFeaturedEducationalPrograms();

        $sliderPrevisualization = $this->getPrevisualizationSlider();

        return view("home", [
            'resources' =>
            [
                "resources/js/home.js",
                "resources/js/carrousel.js",
                "resources/js/slider.js"
            ],
            'featured_courses' => $featured_courses,
            'general_options' => $general_options,
            'educational_resources' => $educational_resources,
            "categories" => $categories,
            "lanes_preferences" => $lanes_preferences,
            "filtered_courses_carrousel" => $filtered_courses_carrousel,
            "featuredEducationalPrograms" => $featuredEducationalPrograms,
            "sliderPrevisualization" => $sliderPrevisualization
        ]);
    }

    public function getTeacherCourses(Request $request)
    {
        $items_per_page = $request->items_per_page;

        $courses = CoursesModel::with(['teachers', 'status', 'educationalProgram', 'educationalProgram.status'])
            ->whereHas('teachers', function ($query) {
                $query->where('user_uid', Auth::user()->uid);
            })
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('belongs_to_educational_program', 0)
                        ->whereHas('status', function ($q2) {
                            $q2->where('code', 'DEVELOPMENT');
                        });
                })->orWhere(function ($q) {
                    $q->where('belongs_to_educational_program', 1)
                        ->whereHas('educationalProgram.status', function ($q2) {
                            $q2->where('code', 'DEVELOPMENT');
                        });
                });
            })
            ->paginate($items_per_page);

        return $courses;
    }

    // Obtiene el slider a previsualizar
    private function getPrevisualizationSlider()
    {
        $sliderPrevisualization = false;
        // Recibimos por parámetro la URL de previsualización del slider
        $previsualizeSliderUid = request()->query('previsualize-slider');
        if ($previsualizeSliderUid) {
            $sliderPrevisualization = SlidersPrevisualizationsModel::where('uid', $previsualizeSliderUid)->first();
        }

        return $sliderPrevisualization;
    }

    private function getFeaturedEducationalPrograms()
    {
        $featuredEducationalPrograms = EducationalProgramsModel::select('educational_programs.*', 'califications_avg.average_calification')
            ->leftJoinSub(
                EducationalProgramsAssessmentsModel::select('educational_program_uid', DB::raw('ROUND(AVG(calification), 1) as average_calification'))
                    ->groupBy('educational_program_uid'),
                'califications_avg', // Alias de la subconsulta
                'califications_avg.educational_program_uid',
                '=',
                'educational_programs.uid'
            )
            ->with('courses')
            ->addSelect([
                'total_ects_workload' => CoursesModel::select(DB::raw('SUM(ects_workload)'))
                    ->whereColumn('educational_program_uid', 'educational_programs.uid')
            ])
            ->whereHas('status', function ($query) {
                $query->where('code', 'INSCRIPTION');
            })
            ->where('inscription_finish_date', '>=', now())
            ->get();

        return $featuredEducationalPrograms;
    }

    private function filterFeaturedCoursesCarrousel($featured_courses)
    {
        $coursesBigCarrouselApprovals = CoursesBigCarrouselsApprovalsModel::all()->pluck('course_uid')->toArray();
        $coursesSmallCarrouselApprovals = CoursesSmallCarrouselsApprovalsModel::all()->pluck('course_uid')->toArray();

        $filtered_courses_carrousel = array_reduce($featured_courses->toArray(), function ($carry, $course) use ($coursesBigCarrouselApprovals, $coursesSmallCarrouselApprovals) {
            if ($course['featured_small_carrousel'] && in_array($course['uid'], $coursesSmallCarrouselApprovals)) {
                $carry['small_carrousel'][] = $course;
            }

            if ($course['featured_big_carrousel'] && in_array($course['uid'], $coursesBigCarrouselApprovals)) {
                $carry['big_carrousel'][] = $course;
            }

            return $carry;
        }, ['small_carrousel' => [], 'big_carrousel' => []]);

        return $filtered_courses_carrousel;
    }

    private function getEducationalPrograms()
    {
        $educational_programs = EducationalProgramsModel::get();
        return $educational_programs;
    }

    private function getLanesPreferences()
    {
        $lanes_preferences = null;
        if (Auth::check()) {
            $lanes_preferences = UserLanesModel::where('user_uid', Auth::user()->uid)->get()->pluck('active', 'code')->toArray();
        }

        if (!$lanes_preferences) {
            $lanes_preferences = [
                "courses" => true,
                "resources" => true,
                "programs" => true
            ];
        }

        return $lanes_preferences;
    }

    private function getCategories()
    {
        $categories = CategoriesModel::withCount(['courses' => function ($query) {
            $query->whereHas('status', function ($query) {
                $query->whereIn('code', ['INSCRIPTION', 'ACCEPTED_PUBLICATION']);
            });
        }])->get();

        return $categories;
    }

    public function getActiveCourses(Request $request)
    {
        $items_per_page = $request->items_per_page;

        $user = Auth::user();

        $courses_students = $user->courses_students()->with('status')
            ->whereHas('status', function ($query) {
                $query->where('code', 'DEVELOPMENT');
            })
            ->wherePivot('acceptance_status', 'ACCEPTED')
            ->wherePivot('status', 'ENROLLED')
            ->paginate($items_per_page);

        return response()->json($courses_students);
    }

    public function getInscribedCourses(Request $request)
    {
        $items_per_page = $request->items_per_page;

        $user = Auth::user();
        $courses_students = $user->courses_students()->with('status')
            ->whereHas('status', function ($query) {
                $query->where('code', 'DEVELOPMENT');
            })
            ->wherePivot('status', 'INSCRIBED')
            ->paginate($items_per_page);

        return response()->json($courses_students);
    }

    private function getFeaturedCourses()
    {
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
                $query->where('code', 'INSCRIPTION');
            })
            ->orderBy('califications_avg.average_calification', 'desc')
            ->get();

        return $featured_courses;
    }

    private function getEducationalResources()
    {
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
            ->get();

        return $educational_resources;
    }

    private function saveLanesPreferences(Request $request)
    {

        if (!in_array($request->lane, ["courses", "resources", "programs"])) {
            throw new \Exception("Invalid lane");
        }

        $lane = $request->lane;
        $active = $request->active;

        // Si ya había una preferencia
        $userLane = UserLanesModel::where('user_uid', Auth::user()->uid)->where('code', $lane)->first();

        if ($userLane) {
            $userLane->active = $active;
            $userLane->save();
        } else {
            $userLane = new UserLanesModel();
            $userLane->uid = generate_uuid();
            $userLane->user_uid = Auth::user()->uid;
            $userLane->code = $lane;
            $userLane->active = $active;
            $userLane->save();
        }

        return response()->json(['success' => true]);
    }
}
