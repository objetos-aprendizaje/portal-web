<?php

namespace App\Http\Controllers;

use App\Models\CategoriesModel;
use App\Models\CoursesAssessmentsModel;
use App\Models\CoursesModel;
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

        $featuredEducationalPrograms = $this->getFeaturedEducationalPrograms();

        $educational_resources = $this->getEducationalResources();

        $categories = $this->getCategories();

        // Cursos y programas a destacar en el slider
        $featuredCoursesSlider = $featured_courses->filter(function ($course) {
            return $course->featured_big_carrousel && $course->featured_big_carrousel_approved;
        });

        $featuredEducationalProgramsSlider = $featuredEducationalPrograms->filter(function ($program) {
            return $program->featured_slider && $program->featured_slider_approved;
        });

        // Cursos y programas a destacar en el carrousel
        $featuredCoursesCarrousel = $featured_courses->filter(function ($course) {
            return $course->featured_small_carrousel && $course->featured_small_carrousel_approved;
        });

        $featuredEducationalProgramsCarrousel = $featuredEducationalPrograms->filter(function ($program) {
            return $program->featured_main_carrousel && $program->featured_main_carrousel_approved;
        });

        // Combina los cursos y programas destacados en el carrousel destacado
        $featuredLearningObjectsCarrousel = $featuredCoursesCarrousel->merge($featuredEducationalProgramsCarrousel);

        $lanes_preferences = $this->getLanesPreferences();

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
            "featuredCoursesSlider" => $featuredCoursesSlider,
            "featuredCoursesCarrousel" => $featuredCoursesCarrousel,
            "featuredEducationalProgramsSlider" => $featuredEducationalProgramsSlider,
            "featuredEducationalProgramsCarrousel" => $featuredEducationalProgramsCarrousel,
            "featuredEducationalPrograms" => $featuredEducationalPrograms,
            "lanes_preferences" => $lanes_preferences,
            "sliderPrevisualization" => $sliderPrevisualization,
            "featuredLearningObjectsCarrousel" => $featuredLearningObjectsCarrousel,
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
            ->addSelect([
                'total_ects_workload' => CoursesModel::select(DB::raw('SUM(ects_workload)'))
                    ->whereColumn('educational_program_uid', 'educational_programs.uid')
            ])
            ->with("status")
            ->whereHas('status', function ($query) {
                $query->whereIn('code', ['ACCEPTED_PUBLICATION', 'INSCRIPTION']);
            })
            ->select(
                "uid",
                "educational_program_status_uid",
                "name as title",
                "image_path",
                "description",
                "inscription_start_date",
                "inscription_finish_date",
                "realization_start_date",
                "realization_finish_date",
                "average_calification",
                "featured_main_carrousel",
                "featured_main_carrousel_approved",
                "featured_slider",
                "featured_slider_approved",
                "featured_slider_image_path",
                "featured_slider_title",
                "featured_slider_description",
                "featured_slider_color_font"
            )
            ->addSelect([
                'ects_workload' => CoursesModel::select(DB::raw('SUM(ects_workload)'))
                    ->whereColumn('educational_program_uid', 'educational_programs.uid')
            ])
            ->get()
            ->map(function ($program) {
                $program->type = 'educationalProgram';
                return $program;
            });

        return $featuredEducationalPrograms;
    }

    private function getLanesPreferences()
    {
        $lanes_preferences = null;
        if (Auth::check()) {
            $lanes_preferences = UserLanesModel::where('user_uid', Auth::user()->uid)->get()->pluck('active', 'code')->toArray();

            if (!isset($lanes_preferences['courses'])) {
                $lanes_preferences['courses'] = true;
            }

            if (!isset($lanes_preferences['resources'])) {
                $lanes_preferences['resources'] = true;
            }

            if (!isset($lanes_preferences['programs'])) {
                $lanes_preferences['programs'] = true;
            }
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
                $query->whereIn('code', ['ACCEPTED_PUBLICATION', 'INSCRIPTION']);
            })
            ->orderBy('califications_avg.average_calification', 'desc')
            ->get()
            ->map(function ($course) {
                $course->type = 'course';
                return $course;
            });

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

    public function saveLanesPreferences(Request $request)
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
