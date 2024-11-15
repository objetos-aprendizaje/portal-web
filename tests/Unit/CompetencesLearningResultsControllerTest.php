<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\CompetencesModel;
use App\Models\LearningResultsModel;
use App\Models\CompetenceFrameworksModel;
use App\Exceptions\OperationFailedException;
use App\Models\CompetenceFrameworksLevelsModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Profile\CompetencesLearningResultsController;

class CompetencesLearningResultsControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Prueba que la vista de competencias y resultados de aprendizaje se carga correctamente con los datos necesarios
     */
    public function testIndexLoadsCompetencesLearningResultsPageWithCorrectData()
    {
        // Crear un usuario y autenticarlo
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        $this->actingAs($user);

        // Crear un framework de competencias con niveles, subcompetencias y resultados de aprendizaje
        $competenceFramework = CompetenceFrameworksModel::factory()->create()->first();

        CompetenceFrameworksLevelsModel::factory()->count(3)->create(
            [
                'competence_framework_uid' => $competenceFramework->uid
            ]
        );

        CompetencesModel::factory()->count(3)->create([
            'competence_framework_uid' => $competenceFramework->uid
        ]);

        $subCompetences =  CompetencesModel::get();

        // $subCompetences = CompetenceFrameworksModel::factory()->count(3)->create([
        //     'parent_competence_uid' => $competenceFramework->uid
        // ]);

        // Crear resultados de aprendizaje asociados a las subcompetencias
        foreach ($subCompetences as $subCompetence) {
            $learningResults = LearningResultsModel::factory()->count(2)->create(
                [
                    'competence_uid' => $subCompetence->uid
                ]
            );
        }

        // Simular que el usuario ha seleccionado algunos resultados de aprendizaje
        $learningResultsUser = LearningResultsModel::get();

        $user->learningResultsPreferences()->attach($learningResultsUser->pluck('uid')->toArray());

        // Hacer una solicitud GET a la ruta de competencias y resultados de aprendizaje
        $response = $this->get(route('competences-learning-results'));

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que la vista correcta se cargue
        $response->assertViewIs('profile.competences_learning_results.index');

        // Verificar que los datos de las competencias y los resultados de aprendizaje se pasan a la vista
        $response->assertViewHas('variables_js', function ($variables_js) use ($learningResultsUser) {
            return isset($variables_js['competencesLearningResults']) && isset($variables_js['learningResultsUserSelected']) &&
                $variables_js['learningResultsUserSelected'] === $learningResultsUser->pluck('uid')->toArray();
        });

        // Verificar que los recursos JavaScript y la página actual se pasan correctamente
        $response->assertViewHas('resources', [
            'resources/js/profile/competences_learning_results.js',
            'resources/js/renderer_infinite_tree.js'
        ]);
        $response->assertViewHas('currentPage', 'competences_learning_results');
        $response->assertViewHas('page_title', 'Configuración de competencias y resultados de aprendizaje');
    }

    /**
     * @test
     * Prueba que el mapeo de competencias se realiza correctamente
     */
    public function testMapCompetencesTransformsDataCorrectly()
    {
        // Crear un framework de competencias con subcompetencias y resultados de aprendizaje
        $competenceFramework = CompetenceFrameworksModel::factory()->create()->first();

        CompetencesModel::factory()->count(3)->create([
            'competence_framework_uid' => $competenceFramework->uid
        ]);

        $subCompetences =  CompetencesModel::get();

        // Crear resultados de aprendizaje asociados a las subcompetencias
        foreach ($subCompetences as $subCompetence) {
            $learningResults = LearningResultsModel::factory()->count(2)->create(
                [
                    'competence_uid' => $subCompetence->uid
                ]
            );
        }

        // Obtener los datos mapeados
        $competencesLearningResults = $competenceFramework->with([
            'allSubcompetences.learningResults',
        ])->get()->toArray();

        $controller = new CompetencesLearningResultsController();
        $mappedCompetences = $controller->mapCompetences($competencesLearningResults);

        // Verificar que el mapeo de las competencias se ha hecho correctamente
        $this->assertIsArray($mappedCompetences);
        $this->assertNotEmpty($mappedCompetences);
        $this->assertArrayHasKey('children', $mappedCompetences[0]);
        $this->assertArrayHasKey('id', $mappedCompetences[0]);
        $this->assertEquals('competence', $mappedCompetences[0]['type']);
    }

    /**
     * @test
     * Prueba que los resultados de aprendizaje se guarden correctamente para el usuario autenticado
     */
    public function testSaveLearningResultsSavesCorrectly()
    {
        // Buscar un usuario y autenticarlo    
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        $this->actingAs($user);

        $competence = CompetencesModel::factory()->create()->first();

        // Crear algunos resultados de aprendizaje
        LearningResultsModel::factory()->count(5)->create([
            'competence_uid' => $competence->uid,
        ]);

        $learningResults = LearningResultsModel::get();

        // Datos simulados enviados desde el formulario
        $requestData = [
            'learningResults' => $learningResults->pluck('uid')->toArray(),
        ];

        // Hacer la solicitud POST a la ruta de guardar resultados de aprendizaje
        $response = $this->post('/profile/competences_learning_results/save_learning_results', $requestData);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que el mensaje correcto se devuelve en el JSON
        $response->assertJson(['message' => 'Resultados de aprendizaje guardados correctamente']);

        // Verificar que los resultados de aprendizaje del usuario se han sincronizado correctamente
        foreach ($learningResults as $result) {
            $this->assertDatabaseHas('user_learning_results_preferences', [
                'user_uid' => $user->uid,
                'learning_result_uid' => $result->uid,
            ]);
        }
    }

    /**
     * @test
     * Prueba que lanza una excepción si se seleccionan más de 100 resultados de aprendizaje
     */
    public function testSaveLearningResultsThrowsExceptionIfMoreThan100Selected()
    {

        // Desactivar el manejo automático de excepciones para capturar la excepción
        $this->withoutExceptionHandling();

        // Buscar un usuario y autenticarlo    
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        $this->actingAs($user);

        // Crear más de 100 resultados de aprendizaje
        LearningResultsModel::factory()->withCompetence()->count(101)->create();

        $learningResults = LearningResultsModel::all();

        // Datos simulados enviados desde el formulario
        $requestData = [
            'learningResults' => $learningResults->pluck('uid')->toArray(),
        ];

        $this->expectException(OperationFailedException::class);
        $this->expectExceptionMessage('No puedes seleccionar más de 100 resultados de aprendizaje');

        // Hacer la solicitud POST a la ruta de guardar resultados de aprendizaje
        $this->post('/profile/competences_learning_results/save_learning_results', $requestData);
    }
}
