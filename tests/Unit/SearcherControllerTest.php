<?php

namespace Tests\Unit;

use App\User;
use Tests\TestCase;
use Illuminate\Http\Request;
use App\Models\CategoriesModel;
use App\Models\CompetencesModel;
use App\Models\HeaderPagesModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use App\Exceptions\OperationFailedException;
use App\Http\Controllers\SearcherController;
use Illuminate\Foundation\Testing\RefreshDatabase;


class SearcherControllerTest extends TestCase
{
    /**
 * @group configdesistema
 */
    use RefreshDatabase;

/**
 * @testdox Inicialización de inicio de sesión
 */
    public function setUp(): void {
        parent::setUp();
        $this->withoutMiddleware();

    }

/**
* @test function index
*/
    public function testSearcherPageLoads()
    {


         // Simular categorías con subcategorías
        $category1 = CategoriesModel::factory()->create(['uid' => 1, 'name' => 'Category 1'])->first();

        $subcategory1 = CategoriesModel::factory()->create(['uid' => 2, 'name' => 'Subcategory 1', 'parent_category_uid' => $category1->uid]);
        $subcategory2 = CategoriesModel::factory()->create(['uid' => 3, 'name' => 'Subcategory 2', 'parent_category_uid' => $category1->uid]);

        $category2 = CategoriesModel::factory()->create(['uid' => 4, 'name' => 'Category 2']);

        // Simular competencias con subcompetencias
        $competence1 = CompetencesModel::factory()->create(['uid' => 1, 'name' => 'Competence 1'])->first();
        $subcompetence1 = CompetencesModel::factory()->create(['uid' => 2, 'name' => 'Subcompetence 1', 'parent_competence_uid' => $competence1->uid]);
        $subcompetence2 = CompetencesModel::factory()->create(['uid' => 3, 'name' => 'Subcompetence 2', 'parent_competence_uid' => $competence1->uid]);

        $competence2 = CompetencesModel::factory()->create(['uid' => 4, 'name' => 'Competence 2']);

          // Crear un mock de la configuración general
          app()->instance('general_options', ['learning_objects_appraisals' => true]);

          $footer_pages = [
              ['slug' => 'page1', 'name' => 'Page 1'],
              ['slug' => 'page2', 'name' => 'Page 2'],
          ];
          View::share('footer_pages', $footer_pages);

           // Define la variable $fonts con todas las claves necesarias
           $fonts = [
              'truetype_regular_file_path' => asset('fonts/robot/roboto.ttf'), // Cambia esto a la ruta real
              'woff_regular_file_path' => asset('fonts/robot/roboto.woff'), // Cambia esto a la ruta real
              'woff2_regular_file_path' => asset('fonts/robot/roboto.woff2'), // Cambia esto a la ruta real
              'embedded_opentype_regular_file_path' => asset('fonts/robot/roboto.eot'), // Cambia esto a la ruta real
              'opentype_regular_input_file' => asset('fonts/robot/roboto.otf'), // Cambia esto a la ruta real
              'svg_regular_file_path' => asset('fonts/robot/roboto.svg'), // Asegúrate de incluir esta clave
              'truetype_medium_file_path' => asset('fonts/robot/roboto-medium.ttf'), // Replace with the actual path
              'woff_medium_file_path' => asset('fonts/robot/roboto-medium.woff'),
              'woff2_medium_file_path' => asset('fonts/robot/roboto-medium.woff2'),
              'embedded_opentype_medium_file_path' => asset('fonts/robot/roboto.eot'),
              'opentype_medium_file_path' => asset('fonts/robot/roboto.otf'),
              'svg_medium_file_path' => asset('fonts/robot/roboto.svg'),
              'truetype_bold_file_path' =>  asset('fonts/robot/roboto-bold.ttf'),
              'woff_bold_file_path' => asset('fonts/robot/roboto-bold.woff'),
              'woff2_bold_file_path' => asset('fonts/robot/roboto-bold.woff2'),
              'embedded_opentype_bold_file_path' => asset('fonts/robot/roboto.eot'),
              'opentype_bold_file_path' => asset('fonts/robot/roboto.otf'),
              'svg_bold_file_path' => asset('fonts/robot/roboto.svg'),
          ];


          // Comparte la variable $fonts para esta prueba
          View::share('fonts', $fonts);

          $headerPages = HeaderPagesModel::whereNull('header_page_uid')->with('headerPagesChildren')->orderBy('order', 'asc')->get();

          // Comparte la variable $header_pages para esta prueba
          View::share('header_pages', $headerPages);


        // Realizar una solicitud GET a la ruta /searcher
        $response = $this->get('/searcher');

        // Verificar que la respuesta sea exitosa
        $response->assertStatus(200);

        // Verificar que se esté utilizando la vista correcta
        $response->assertViewIs('searcher');

        // Verificar que se pasen los datos correctos a la vista
        $response->assertViewHas('general_options');
        $response->assertViewHas('categories');
        $response->assertViewHas('competences');
        $response->assertViewHas('variables_js');

        // // Verificar el contenido de las variables JS
        // $viewData = $response->viewData();
        // $this->assertEquals(collect([$category1->toArray(), $category2->toArray()]), collect($viewData['categories']));
        // $this->assertEquals(collect([$competence1->toArray(), $competence2->toArray()]), collect($viewData['competences']));
        // $this->assertTrue($viewData['variables_js']['learning_objects_appraisals']);
    }

/**
 * @test Retorna LearningObjects con tipo de recurso por defecto
 */

    public function testReturnsLearningObjectsDefaultResourceTypes()
    {
        $request = Request::create('/searcher/get_learning_objects', 'POST', [
            'itemsPerPage' => 10,
            'filters' => []
        ]);

        $controller = new SearcherController();
        $response = $controller->getLearningObjects($request);

        $this->assertEquals(200, $response->status());
    }

/** @test */
    public function testReturnsLearningObjectsWithEspecificResourceTypes()
    {
        $request = Request::create('/searcher/get_learning_objects', 'POST', [
            'resourceTypes' => ['courses'],
            'itemsPerPage' => 10,
            'filters' => []
        ]);

        $controller = new SearcherController();
        $response = $controller->getLearningObjects($request);

        $this->assertEquals(200, $response->status());

    }

/** @test Validación de filtros*/
    public function testValidatesFilters()
    {

        $this->expectException(OperationFailedException::class);
        $this->expectExceptionMessage("El estado del objeto de aprendizaje no es válido");

        $request = Request::create('/searcher/get_learning_objects', 'POST', [
            'itemsPerPage' => 10,
            'filters' => ['learningObjectStatus' => 'INVALID_STATUS']
        ]);

        $controller = new SearcherController();
        $response = $controller->getLearningObjects($request);

        $this->assertEquals(200, $response->status());
    }

    /** @test Retorna Learning Objects con status válido*/
    public function testReturnsLearningObjectsValidStatus()
    {
        // Simulamos una consulta válida
        $request = Request::create('/searcher/get_learning_objects', 'POST', [
            'itemsPerPage' => 10,
            'filters' => ['learningObjectStatus' => 'INSCRIPTION']
        ]);

        $controller = new SearcherController();
        $response = $controller->getLearningObjects($request);

        $this->assertEquals(200, $response->status());

    }

    /** @test */
    public function testCallsSearchApiWhenFilterIsPresent()
    {
        // Simulamos la respuesta del API
        Http::fake([
            env('API_SEARCH_URL') . '/search_learning_objects' => Http::sequence()
                ->push(['data' => ['uuid1', 'uuid2']])
        ]);

        $request = Request::create('/searcher/get_learning_objects', 'POST', [
            'itemsPerPage' => 10,
            'filters' => ['search' => 'test']
        ]);

        $controller = new SearcherController();
        $response = $controller->getLearningObjects($request);

        $this->assertEquals(200, $response->status());

    }


    /** @test Orden de Learning Objects  */
    public function testOrdersLearningObjectsByCloser()
    {
        $request = Request::create('/searcher/get_learning_objects', 'POST', [
            'resourceTypes' => ['courses'],
            'itemsPerPage' => 10,
            'orderBy' => 'closer',
            'filters' => []
        ]);

        $controller = new SearcherController();
        $response = $controller->getLearningObjects($request);

        $this->assertEquals(200, $response->status());

    }

/** @test */
    public function testOrdersLearningObjectsByPuntuation()
    {
        $request = Request::create('/searcher/get_learning_objects', 'POST', [
            'resourceTypes' => ['courses'],
            'itemsPerPage' => 10,
            'orderBy' => 'puntuation',
            'filters' => []
        ]);

        $controller = new SearcherController();
        $response = $controller->getLearningObjects($request);

        $this->assertEquals(200, $response->status());

    }
}
