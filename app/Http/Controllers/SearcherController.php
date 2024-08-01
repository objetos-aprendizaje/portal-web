<?php

namespace App\Http\Controllers;

use App\Exceptions\OperationFailedException;
use App\Models\CategoriesModel;
use App\Models\CompetencesModel;
use App\Models\CoursesAssessmentsModel;
use App\Models\CoursesModel;
use App\Models\EducationalProgramsModel;
use App\Models\EducationalResourcesAssessmentsModel;
use App\Models\EducationalResourcesModel;
use Illuminate\Http\Request;
use App\Models\GeneralOptionsModel;
use Illuminate\Support\Facades\DB;


class SearcherController extends Controller
{

    public function index()
    {

        $general_options = GeneralOptionsModel::all()->pluck('option_value', 'option_name')->toArray();

        $categories = CategoriesModel::whereNull('parent_category_uid')->with('subcategories')->get()->toArray();

        $competences = CompetencesModel::whereNull('parent_competence_uid')->with('subcompetences')->orderBy('name', 'ASC')->get()->toArray();

        return view("searcher", [
            "general_options" => $general_options,
            "page_title" => "Búsqueda de objetos de aprendizaje | POA",
            "categories" => $categories,
            "competences" => $competences,
            "flatpickr" => true,
            "resources" => [
                "resources/js/search.js"
            ],
            "treeselect" => true,
            "variables_js" => [
                "categories" => $categories,
                "competences" => $competences,
                "learning_objects_appraisals" => app('general_options')['learning_objects_appraisals'] ? true : false
            ]
        ]);
    }

    public function getLearningObjects(Request $request)
    {
        $resource_types = $request->get("resourceTypes");

        if (empty($resource_types)) {
            $resource_types = ["courses", "programs", "resources"];
        }

        $learning_objects = [];

        $queries = [];

        $itemsPerPage = $request->get("itemsPerPage");
        $filters =  $request->get('filters');

        $this->validateFilters($filters);

        if (env('ENABLED_API_SEARCH')) {
            if (isset($filters['search'])) {
                $response = $this->searchApi($filters['search']);
                $filters['add_uuids_to_search'] = $response->data;
            }
        }

        if (in_array("courses", $resource_types)) {
            $queries[] = $this->buildCoursesQuery($filters);
        }

        if (in_array("programs", $resource_types)) {
            $queries[] = $this->buildEducationalProgramsQuery($filters);
        }

        if (in_array("resources", $resource_types)) {
            $queries[] = $this->buildEducationalResourcesQuery($filters);
        }

        $learning_objects_query = array_shift($queries);

        foreach ($queries as $query) {
            $learning_objects_query->unionAll($query);
        }

        $orderBy = $request->get("orderBy");

        if ($orderBy == "closer") {
            $learning_objects_query->orderByRaw('ISNULL(inscription_start_date), inscription_start_date ASC');
        } else if ($orderBy == "puntuation") {
            $learning_objects_query->orderBy('average_calification', 'desc');
        }

        $learning_objects = $learning_objects_query->orderBy('inscription_start_date')->paginate($itemsPerPage);

        return response()->json($learning_objects);
    }

    private function validateFilters($filters)
    {
        if (isset($filters['learningObjectStatus']) && !in_array($filters['learningObjectStatus'], ['INSCRIPTION', 'ENROLLING', 'DEVELOPMENT', 'FINISHED'])) {
            throw new OperationFailedException("El estado del objeto de aprendizaje no es válido");
        }
    }

    private function searchApi($searchText)
    {
        $endpoint = env('API_SEARCH_URL') . '/search_learning_objects';

        $data = (object) [
            "text" => $searchText,
        ];

        $header = [
            "API-KEY" => env('API_SEARCH_KEY')
        ];

        $response = guzzle_call($endpoint, $data, $header, 'POST');

        $response = json_decode($response);

        return $response;
    }

    private function buildEducationalProgramsQuery($filters = [])
    {
        $educational_programs_query = EducationalProgramsModel::select([
            'educational_programs.uid',
            'educational_programs.name as title',
            'educational_programs.description',
            DB::raw("null as average_calification"),
            'inscription_start_date',
            'inscription_finish_date',
            DB::raw("null as realization_start_date"),
            DB::raw("null as realization_finish_date"),
            DB::raw("null as status_code"),
            DB::raw("'educational_program' as learning_object_type"),
            'image_path',
        ])
            ->leftJoin('educational_program_statuses', 'educational_programs.educational_program_status_uid', '=', 'educational_program_statuses.uid')
            ->withCount([
                'courses as ects_workload' => function ($query) {
                    $query->select(DB::raw("SUM(ects_workload)"));
                }
            ])
            ->with('courses', 'courses.average_calification');

        if (isset($filters['learningObjectStatus'])) {
            $educational_programs_query->where('educational_program_statuses.code', $filters['learningObjectStatus']);
        } else {
            $educational_programs_query->whereIn('educational_program_statuses.code', ['INSCRIPTION', 'DEVELOPMENT', 'ENROLLING', 'FINISHED']);
        }

        if (isset($filters['categories'])) {
            $educational_programs_query->with(['courses.categories'])->whereHas('courses.categories', function ($query) use ($filters) {
                $query->whereIn('category_uid', $filters['categories']);
            });
        }

        if (isset($filters['competences'])) {
            $competences = $filters['competences'];

            $educational_programs_query->with([
                'courses.blocks', 'courses.blocks.competences'
            ])->whereHas('courses.blocks.competences', function ($query) use ($competences) {
                $query->whereIn('competences.uid', $competences);
            });
        }

        if (isset($filters['inscription_start_date']) && isset($filters['inscription_finish_date'])) {
            $educational_programs_query->whereBetween('inscription_start_date', [$filters['inscription_start_date'], $filters['inscription_finish_date']]);
        }


        if (isset($filters['search'])) {
            $educational_programs_query->where('educational_programs.name', 'like', '%' . $filters['search'] . '%')->orWhere('description', 'like', '%' . $filters['search'] . '%');
        }

        return $educational_programs_query;
    }

    private function buildEducationalResourcesQuery($filters = [])
    {

        $educational_resources_query = EducationalResourcesModel::select([
            'educational_resources.uid',
            'educational_resources.title',
            'educational_resources.description',
            'califications_avg.average_calification',
            DB::raw("null as inscription_start_date"),
            DB::raw("null as inscription_finish_date"),
            DB::raw("null as realization_start_date"),
            DB::raw("null as realization_finish_date"),
            'educational_resource_statuses.code as status_code',
            DB::raw("'educational_resource' as learning_object_type"),
            'image_path',
            DB::raw("null as ects_workload"),
        ])
            ->whereHas('status', function ($query) {
                $query->where('code', 'PUBLISHED');
            })
            ->join('educational_resource_statuses', 'educational_resources.status_uid', '=', 'educational_resource_statuses.uid')
            ->leftJoinSub(
                EducationalResourcesAssessmentsModel::select('educational_resources_uid', DB::raw('ROUND(AVG(calification), 1) as average_calification'))
                    ->groupBy('educational_resources_uid'),
                'califications_avg',
                'califications_avg.educational_resources_uid',
                '=',
                'educational_resources.uid'
            );

        if (isset($filters['categories'])) {
            $categories = $filters['categories'];
            $educational_resources_query->with('categories')->whereHas('categories', function ($query) use ($categories) {
                $query->whereIn('category_uid', $categories);
            });
        }

        if (isset($filters['assessments'])) {
            $educational_resources_query->where('califications_avg.average_calification', $filters['assessments']);
        }

        if (isset($filters['search'])) {
            $educational_resources_query->where('title', 'like', '%' . $filters['search'] . '%')->orWhere('description', 'like', '%' . $filters['search'] . '%');
            if (isset($filters['add_uuids_to_search'])) {
                $educational_resources_query->orWhereIn('educational_resources.uid', $filters['add_uuids_to_search']);
            }
        }

        return $educational_resources_query;
    }

    private function buildCoursesQuery($filters = [])
    {
        $courses_query = CoursesModel::select([
            'courses.uid',
            'courses.title',
            'courses.description',
            'califications_avg.average_calification',
            'inscription_start_date',
            'inscription_finish_date',
            'realization_start_date',
            'realization_finish_date',
            'course_statuses.code as status_code',
            DB::raw("'course' as learning_object_type"),
            'image_path',
            'ects_workload',
        ])
            ->join('course_statuses', 'courses.course_status_uid', '=', 'course_statuses.uid')
            ->leftJoinSub(
                CoursesAssessmentsModel::select('course_uid', DB::raw('ROUND(AVG(calification)) as average_calification'))
                    ->groupBy('course_uid'),
                'califications_avg', // Alias de la subconsulta
                'califications_avg.course_uid',
                '=',
                'courses.uid'
            )
            ->whereNull('educational_program_uid');

        if (isset($filters['learningObjectStatus'])) {
            $courses_query->where('course_statuses.code', $filters['learningObjectStatus']);
        } else {
            $courses_query->whereIn('course_statuses.code', ['INSCRIPTION', 'DEVELOPMENT', 'ENROLLING', 'FINISHED']);
        }

        if (isset($filters['categories'])) {
            $courses_query->with('categories')->whereHas('categories', function ($query) use ($filters) {
                $query->whereIn('category_uid', $filters['categories']);
            });
        }

        if (isset($filters['competences'])) {
            $competences = $filters['competences'];
            $courses_query->with([
                'blocks' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'blocks.competences'
            ])->whereHas('blocks.competences', function ($query) use ($competences) {
                $query->whereIn('competences.uid', $competences);
            });
        }

        if (isset($filters['inscription_start_date']) && isset($filters['inscription_finish_date'])) {
            $courses_query->whereBetween('inscription_start_date', [$filters['inscription_start_date'], $filters['inscription_finish_date']]);
        }

        if (isset($filters['realization_start_date']) && isset($filters['realization_finish_date'])) {
            $courses_query->whereBetween('realization_start_date', [$filters['realization_start_date'], $filters['realization_finish_date']]);
        }

        if (isset($filters['modalityPayment'])) {
            if ($filters['modalityPayment'] == "FREE") $courses_query->where('cost', 0);
            else if ($filters['modalityPayment'] == "PAID") $courses_query->where('cost', '>', 0);
        }

        if (isset($filters['assessments'])) {
            $courses_query->where('califications_avg.average_calification', $filters['assessments']);
        }

        if (isset($filters['search'])) {
            $courses_query->where(function ($query) use ($filters) {
                $query->where('title', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');

                if (isset($filters['add_uuids_to_search'])) {
                    $query->orWhereIn('courses.uid', $filters['add_uuids_to_search']);
                }
            });
        }

        return $courses_query;
    }
}
