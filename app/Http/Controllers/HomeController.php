<?php

namespace App\Http\Controllers;

use App\Exceptions\OperationFailedException;
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
use App\Services\EmbeddingsService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class HomeController extends BaseController
{
    protected $embeddingsService;

    public function __construct(EmbeddingsService $embeddingsService)
    {
        $this->embeddingsService = $embeddingsService;
    }
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
            "page_title" => "Inicio"
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

        $this->transformCollection($courses);

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

        $this->transformCollection($courses_students);

        return response()->json($courses_students);
    }

    public function getRecommendedCourses(Request $request)
    {
        if (!app('general_options')['enabled_recommendation_module']) {
            throw new OperationFailedException();
        }

        $items_per_page = $request->items_per_page;
        $page = $request->page;

        // Cursos en los que el usuario está inscrito
        $coursesUser = auth()->user()->courses_students;

        if (!$coursesUser->count()) {
            throw new OperationFailedException("No courses found for the user");
        }
        $categoriesUser = auth()->user()->categories->pluck('uid')->toArray();

        $learningResultsUser = auth()->user()->learningResultsPreferences->pluck('uid')->toArray();

        $similarCourses = $this->embeddingsService->getSimilarCoursesList($coursesUser, $categoriesUser, $learningResultsUser, $items_per_page, $page);

        $this->transformCollection($similarCourses);

        return response()->json($similarCourses);
    }

    public function getRecommendedEducationalResources(Request $request)
    {
        if (!app('general_options')['enabled_recommendation_module']) {
            throw new OperationFailedException();
        }

        $items_per_page = $request->items_per_page;
        $page = $request->page;

        // Recursos educativos con los que el usuario ha interactuado
        $educationalResourcesUser = auth()->user()->educationalResources;

        if (!$educationalResourcesUser->count()) {
            throw new OperationFailedException("No educational resources found for the user");
        }
        $categoriesUser = auth()->user()->categories->pluck('uid')->toArray();
        $learningResultsUser = auth()->user()->learningResultsPreferences->pluck('uid')->toArray();

        $similarEducationalResources = $this->embeddingsService->getSimilarEducationalResourcesList($educationalResourcesUser, $categoriesUser, $learningResultsUser, $items_per_page, $page);

        $this->transformCollection($similarEducationalResources);

        return response()->json($similarEducationalResources);
    }

    public function getMyEducationalResources(Request $request)
    {
        $items_per_page = $request->items_per_page;

        $educationalResources = EducationalResourcesModel::select("educational_resources.*")
            ->distinct()
            ->whereIn('uid', function ($query) {
                $query->select('educational_resource_uid')
                    ->from('educational_resource_access')
                    ->where('user_uid', Auth::user()->uid);
            })
            ->paginate($items_per_page);

        $this->transformCollection($educationalResources);

        return response()->json($educationalResources);
    }

    public function getRecommendedItinerary(Request $request)
    {
        $itemsPerPage = $request->items_per_page;

        // Resultados de aprendizaje del alumno
        $userLearningResults = $this->getLearningResultsStudent();

        // Recursos de aprendizaje del alumno cubiertos
        $userLearningResultsCovered = $this->getCoveredLearningResults($userLearningResults);

        $userLearningResultsNotCovered = array_diff($userLearningResults, $userLearningResultsCovered);

        // Cursos con resultados de aprendizaje del alumno
        $paginatedCourses = $this->getCoursesLearningResultsStudent($userLearningResultsNotCovered, $itemsPerPage);

        // Filtrar cursos con resultados de aprendizaje del alumno
        $paginatedFilteredCourses = $this->filterCoursesLearningResults($paginatedCourses, $userLearningResultsNotCovered, $itemsPerPage);

        $this->transformCollection($paginatedFilteredCourses);

        return response()->json($paginatedFilteredCourses);
    }

    private function getLearningResultsStudent()
    {
        $userLearningResults = Auth::user()->learningResultsPreferences()
            ->get()
            ->pluck("uid")->toArray();

        return $userLearningResults;
    }

    private function getCoveredLearningResults($userLearningResults)
    {
        $userLearningResultsCovered = CoursesModel::whereHas('students', function ($query) {
            $query->where('user_uid', Auth::user()->uid);
        })
            ->whereHas('blocks.learningResults', function ($query) use ($userLearningResults) {
                $query->whereIn('learning_results.uid', $userLearningResults);
            })
            ->with('blocks.learningResults', function ($query) use ($userLearningResults) {
                $query->whereIn('learning_results.uid', $userLearningResults);
            })
            ->get()->pluck('blocks')->flatten()->pluck('learningResults')->flatten()->pluck('uid')->unique()->toArray();

        return $userLearningResultsCovered;
    }

    private function getCoursesLearningResultsStudent($userLearningResultsNotCovered, $itemsPerPage)
    {
        $paginatedCourses = CoursesModel::with(['status', 'blocks.learningResults' => function ($query) use ($userLearningResultsNotCovered) {
            $query->whereIn('learning_results.uid', $userLearningResultsNotCovered);
        }])
            ->whereHas('blocks.learningResults', function ($query) use ($userLearningResultsNotCovered) {
                $query->whereIn('learning_results.uid', $userLearningResultsNotCovered);
            })
            ->whereHas('status', function ($query) {
                $query->where('code', 'INSCRIPTION');
            })
            ->where("belongs_to_educational_program", false)
            ->selectSub(function ($query) use ($userLearningResultsNotCovered) {
                $query->from('learning_results as l_r')
                    ->join("learning_results_blocks as l_r_b", "l_r.uid", "=", "l_r_b.learning_result_uid")
                    ->join("course_blocks as c_b", "l_r_b.course_block_uid", "=", "c_b.uid")
                    ->join("courses as c", "c_b.course_uid", "=", "c.uid")
                    ->where("c.belongs_to_educational_program", false)
                    ->whereIn('l_r.uid', $userLearningResultsNotCovered)
                    ->whereColumn('c.uid', 'courses.uid')
                    ->selectRaw('count(l_r.uid) as learning_results_count');
            }, 'learning_results_count')
            ->addSelect('courses.*')
            ->orderBy('learning_results_count', 'desc')->paginate($itemsPerPage);

        return $paginatedCourses;
    }

    private function filterCoursesLearningResults($paginatedCourses, $userLearningResultsNotCovered, $itemsPerPage)
    {
        $filteredCourses = [];
        $coveredLearningResults = collect();
        foreach ($paginatedCourses as $course) {
            $courseLearningResults = $course->blocks->pluck('learningResults')->flatten()->pluck('uid')->unique();

            // Verificar si el curso cubre algún resultado de aprendizaje no cubierto
            $uncoveredResults = $courseLearningResults->diff($coveredLearningResults);

            if ($uncoveredResults->isNotEmpty()) {
                $filteredCourses[] = $course;
                $coveredLearningResults = $coveredLearningResults->merge($uncoveredResults);

                // Detener la iteración si todos los resultados de aprendizaje no cubiertos están cubiertos
                if ($coveredLearningResults->intersect($userLearningResultsNotCovered)->count() == count($userLearningResultsNotCovered)) {
                    break;
                }
            }
        }

        // Convertir los cursos filtrados a una colección
        $filteredCoursesCollection = collect($filteredCourses);

        // Crear una nueva instancia de LengthAwarePaginator para los cursos filtrados
        $paginatedFilteredCourses = new LengthAwarePaginator(
            $filteredCoursesCollection->forPage($paginatedCourses->currentPage(), $itemsPerPage),
            $filteredCoursesCollection->count(),
            $itemsPerPage,
            $paginatedCourses->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $paginatedFilteredCourses;
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

            ->with("status")
            ->whereHas('status', function ($query) {
                $query->whereIn('code', ['ACCEPTED_PUBLICATION', 'INSCRIPTION']);
            })
            ->select(
                "educational_programs.uid",
                "educational_programs.educational_program_status_uid",
                "educational_programs.name as title",
                "educational_programs.image_path",
                "educational_programs.description",
                "educational_programs.inscription_start_date",
                "educational_programs.inscription_finish_date",
                "educational_programs.realization_start_date",
                "educational_programs.realization_finish_date",
                "califications_avg.average_calification",
                "educational_programs.featured_main_carrousel",
                "educational_programs.featured_main_carrousel_approved",
                "educational_programs.featured_slider",
                "educational_programs.featured_slider_approved",
                "educational_programs.featured_slider_image_path",
                "educational_programs.featured_slider_title",
                "educational_programs.featured_slider_description",
                "educational_programs.featured_slider_color_font",
                DB::raw('(SELECT SUM(CAST(ects_workload AS numeric)) FROM courses WHERE courses.educational_program_uid = educational_programs.uid) as ects_workload')
            )
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

        $this->transformCollection($courses_students);

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

    private function transformCollection($collection)
    {
        $collection->transform(function ($item) {
            return [
                'uid' => $item->uid,
                'title' => $item->title,
                'description' => $item->description,
                'lms_url' => $item->lms_url,
                'realization_start_date' => $item->realization_start_date,
                'realization_finish_date' => $item->realization_finish_date,
                'image_path' => $item->image_path,
                "status_code" => $item->status->code
            ];
        });

        return $collection;
    }
}
