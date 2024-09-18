<?php

namespace Tests\Unit;

use App\User;
use Tests\TestCase;
use App\Jobs\SendEmailJob;
use App\Models\HeaderPagesModel;
use App\Models\GeneralOptionsModel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use App\Models\SuggestionSubmissionEmailsModel;
use Illuminate\Foundation\Testing\RefreshDatabase;


class SuggestionsControllerTest extends TestCase
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
    public function testIndexTheSuggestionsPageJsResources()
    {

        $general_options = GeneralOptionsModel::all()->pluck('option_value', 'option_name')->toArray();
        View::share('general_options', $general_options);

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



        // Realiza una solicitud GET a la ruta /suggestions
        $response = $this->get('/suggestions');

        // Verifica que la respuesta sea un éxito (código 200)
        $response->assertStatus(200);


        // Verifica que se está utilizando la vista correcta
        $response->assertViewIs('suggestions');

        // Verifica que la vista contiene la variable 'resources'
        $response->assertViewHas('resources');

        // Verifica que la variable 'resources' contiene el archivo JavaScript esperado
        $this->assertEquals(
            ["resources/js/suggestions.js"],
            $response->viewData('resources')
        );
    }

/**
 * @test Enviar Suggestion
 */

    public function testSendSuggestion()
    {
        // Simular datos válidos
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'message' => 'This is a test suggestion.',
        ];

        // Simular la creación de correos electrónicos para el envío
        SuggestionSubmissionEmailsModel::factory()->create(['email' => 'recipient@example.com']);

        // Esperar que el trabajo de envío de correo sea despachado
        Mail::fake();

        // Realizar una solicitud POST a la ruta
        $response = $this->postJson('/suggestions/send_suggestion', $data);

        // Verify that the response is successful
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Sugerencia enviada correctamente']);

    }



}
