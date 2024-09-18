<?php

namespace App\Http\Controllers\Profile;

use App\Exceptions\OperationFailedException;
use App\Models\CompetenceFrameworksModel;
use App\Models\LearningResultsModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompetencesLearningResultsController extends BaseController
{

    public function index()
    {
        $competenceFrameworks = CompetenceFrameworksModel::with([
            'levels',
            'allSubcompetences',
            'allSubcompetences.learningResults',
            'allSubcompetences.allSubcompetences',
            'allSubcompetences.allSubcompetences.learningResults'
        ])->get();

        $competencesLearningResultsMapped = $this->mapCompetences($competenceFrameworks->toArray());

        $learningResultsUserSelected = Auth::user()->learningResultsPreferences->pluck('uid')->toArray();

        return view('profile.competences_learning_results.index', [
            "resources" => [
                "resources/js/profile/competences_learning_results.js",
                "resources/js/renderer_infinite_tree.js"
            ],
            'currentPage' => 'competences_learning_results',
            "page_title" => "Configuración de competencias y resultados de aprendizaje | POA",
            "variables_js" => [
                "competencesLearningResults" => $competencesLearningResultsMapped,
                "learningResultsUserSelected" => $learningResultsUserSelected
            ],
            "infiniteTree" => true
        ]);
    }


    function mapCompetences($competences)
    {
        $mapped = array_map(function ($competence) {
            $mappedCompetence = [
                'id' => $competence['uid'],
                'name' => $competence['name'],
                'children' => [],
                'type' => 'competence',
                'showCheckbox' => true,
            ];

            // Si hay subcompetences, aplicar la función de manera recursiva
            if (!empty($competence['all_subcompetences'])) {
                $mappedCompetence['children'] = $this->mapCompetences($competence['all_subcompetences']);
            }

            if (!empty($competence['learning_results'])) {
                foreach ($competence['learning_results'] as $learningResult) {
                    $mappedCompetence['children'][] = [
                        'id' => $learningResult['uid'],
                        'name' => $learningResult['name'],
                        'children' => [],
                        'type' => 'learningResult',
                        'showCheckbox' => true,

                    ];
                }
            }

            return $mappedCompetence;
        }, $competences);

        return $mapped;
    }

    public function saveLearningResults(Request $request) {
        $learningResults = $request->input('learningResults');
        $countLearningResults = count($learningResults);

        if($countLearningResults > 100) {
            throw new OperationFailedException('No puedes seleccionar más de 100 resultados de aprendizaje', 406);
        }

        $learningResultsBd = LearningResultsModel::whereIn('uid', $learningResults)->get();
        Auth::user()->learningResultsPreferences()->sync($learningResultsBd->pluck('uid'));

        return response()->json(['message' => 'Resultados de aprendizaje guardados correctamente']);
    }
}
