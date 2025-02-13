<?php

namespace Tests\Unit;

use App\User;
use Tests\TestCase;
use App\Models\Resource;
use App\Models\UsersModel;
use App\Models\BlocksModel;
use App\Models\CoursesModel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\CategoriesModel;
use App\Models\CompetencesModel;
use App\Models\HeaderPagesModel;
use App\Models\LicenseTypesModel;
use App\Models\GeneralOptionsModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use App\Models\EducationalResourcesModel;
use App\Exceptions\OperationFailedException;
use App\Http\Controllers\SearcherController;
use App\Models\EducationalResourceTypesModel;
use App\Models\EducationalResourceStatusesModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EducationalResourcesAssessmentsModel;
use App\Models\EducationalResourceEmailsContactsModel;


class ResourceInfoControllerTest extends TestCase
{
    /**
     * @group configdesistema
     */
    use RefreshDatabase;

    /**
     * @testdox Inicialización de inicio de sesión
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

    }
    /**
     * @test
     */
    public function testResourceInfoReturns404WhenResourceNotFound()
    {
        // Simular un UID inexistente
        $educational  = EducationalResourcesModel::factory()
            ->withStatus()
            ->withEducationalResourceType()
            ->withCreatorUser()
            ->create();

        EducationalResourceEmailsContactsModel::factory()->create([
            'educational_resource_uid' => $educational->uid,
            'email' => 'info@miemail.com',
        ]);

        $response = $this->get('/resource/' . $educational->uuid);

        // Asegurarse de que la respuesta sea un 404
        $response->assertStatus(404);
    }

    public function testResourceInfoLoadsCorrectlyWhenResourceExists()
    {
        // Reviso  el estado del recurso educativo (asegurándonos que sea PUBLISHED)

        $status = EducationalResourceStatusesModel::where('code', 'PUBLISHED')->first();

        // Crear el tipo de recurso educativo
        $educationalResourceType = EducationalResourceTypesModel::factory()->create()->latest()->first();

        // Crear el tipo de licencia
        $licenseType = LicenseTypesModel::factory()->create()->latest()->first();

        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        // Crear un recurso educativo utilizando las relaciones
        $educationalResource = EducationalResourcesModel::factory()->create([
            'uid' => generate_uuid(),
            'title' => 'Sample Resource',
            'status_uid' => $status->uid,
            'educational_resource_type_uid' => $educationalResourceType->uid,
            'creator_user_uid' => $user->uid,
            'license_type_uid' => $licenseType->uid, // Relacionar con el tipo de licencia
        ])->latest()->first();

        // Crear una evaluación para el recurso educativo (simular calificación)
        EducationalResourcesAssessmentsModel::factory()->create([
            'uid' => generate_uuid(),
            'user_uid' => $user->uid,
            'educational_resources_uid' => $educationalResource->uid,
            'calification' => 4.5,
        ]);

        $general_options = GeneralOptionsModel::all()->pluck('option_value', 'option_name')->toArray();
        View::share('general_options', $general_options);

        $footer_pages = [
            ['slug' => 'page1', 'name' => 'Page 1'],
            ['slug' => 'page2', 'name' => 'Page 2'],
        ];
        View::share('footer_pages', $footer_pages);

        View::share('existsEmailSuggestions', true);

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

        // Realizar la petición HTTP a la ruta del recurso
        $response = $this->get('/resource/' . $educationalResource->uid);

        // Verificar que el estado de respuesta sea 200
        $response->assertStatus(200);

        // Verificar que se está pasando el recurso correcto a la vista
        $response->assertViewHas('educational_resource', function ($viewResource) use ($educationalResource) {
            return $viewResource->uid === $educationalResource->uid;
        });

        // Verificar que la vista contiene el archivo JS esperado
        $response->assertViewHas('resources', function ($resources) {
            return in_array('resources/js/educational_resource_info.js', $resources);
        });
    }

    /**
     * Test para verificar que el recurso exista.
     */
    public function testGetResourceSuccess()
    {
        // Reviso  el estado del recurso educativo (asegurándonos que sea PUBLISHED)

        $status = EducationalResourceStatusesModel::where('code', 'PUBLISHED')->first();

        // Crear el tipo de recurso educativo
        $educationalResourceType = EducationalResourceTypesModel::factory()->create()->latest()->first();

        // Crear el tipo de licencia
        $licenseType = LicenseTypesModel::factory()->create()->latest()->first();

        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        $uid = generate_uuid();
        // Crear un recurso educativo utilizando las relaciones
        $educationalResource = EducationalResourcesModel::factory()->create([
            'uid' => $uid,
            'title' => 'Sample Resource',
            'status_uid' => $status->uid,
            'educational_resource_type_uid' => $educationalResourceType->uid,
            'creator_user_uid' => $user->uid,
            'license_type_uid' => $licenseType->uid, // Relacionar con el tipo de licencia
        ])->latest()->first();


        // Realizar la petición GET a la ruta con el uid del recurso
        $response = $this->get('/resource/get_resource/' . $uid);

        // Verificar que el estado de la respuesta es 200 (OK)
        $response->assertStatus(200);

        // Verificar que los datos del recurso están presentes en la respuesta
        $response->assertJson($educationalResource->toArray());
    }

    /**
     * Test para verificar el caso en el que no se encuentra el recurso.
     */
    public function testGetResourceNotFound()
    {
        // Intentar acceder a un recurso inexistente
        $response = $this->get('/resource/get_resource/' . generate_uuid());

        // Verificar que el estado de la respuesta es 404 (Not Found)
        $response->assertStatus(404);
    }

    public function testCalificateResourceSuccess()
    {
        // Estado del recurso educativo (asegurándonos que sea PUBLISHED)
        $status = EducationalResourceStatusesModel::where("code", "PUBLISHED")->first();

        // Crear el tipo de recurso educativo
        $educationalResourceType = EducationalResourceTypesModel::factory()->create()->latest()->first();

        // Crear el tipo de licencia
        $licenseType = LicenseTypesModel::factory()->create()->latest()->first();

        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        // Crear un recurso educativo utilizando las relaciones
        $educationalResource = EducationalResourcesModel::factory()->create([
            'uid' => generate_uuid(),
            'title' => 'Sample Resource',
            'status_uid' => $status->uid,
            'educational_resource_type_uid' => $educationalResourceType->uid,
            'creator_user_uid' => $user->uid,
            'license_type_uid' => $licenseType->uid,
        ])->latest()->first();

        // Verificar que el recurso educativo fue creado
        $this->assertNotNull($educationalResource, 'El recurso educativo no fue creado correctamente.');

        // Realizar la petición POST a la ruta, enviando la calificación y el UID del recurso
        $response = $this->post('/resource/calificate', [
            'calification' => 4,
            'educational_resource_uid' => $educationalResource->uid
        ]);

        // Verificar que la respuesta es 200 (OK)
        $response->assertStatus(200);

        // Verificar que el mensaje de éxito está presente en la respuesta JSON
        $response->assertJson([
            'message' => 'Se ha registrado correctamente la calificación',
        ]);

        // Verificar que la calificación se ha guardado correctamente en la base de datos
        $this->assertDatabaseHas('educational_resources_assessments', [
            'user_uid' => $user->uid,
            'educational_resources_uid' => $educationalResource->uid,
            'calification' => 4,
        ]);
    }

    public function testCalificateResourceSuccessWithCalification()
    {
        // Estado del recurso educativo (asegurándonos que sea PUBLISHED)
        $status = EducationalResourceStatusesModel::where("code", "PUBLISHED")->first();

        // Crear el tipo de recurso educativo
        $educationalResourceType = EducationalResourceTypesModel::factory()->create()->latest()->first();

        // Crear el tipo de licencia
        $licenseType = LicenseTypesModel::factory()->create()->latest()->first();

        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        // Crear un recurso educativo utilizando las relaciones
        $educationalResource = EducationalResourcesModel::factory()->create([
            'uid' => generate_uuid(),
            'title' => 'Sample Resource',
            'status_uid' => $status->uid,
            'educational_resource_type_uid' => $educationalResourceType->uid,
            'creator_user_uid' => $user->uid,
            'license_type_uid' => $licenseType->uid,
        ])->latest()->first();

        EducationalResourcesAssessmentsModel::factory()->create(
            [
                'educational_resources_uid' => $educationalResource->uid,
                'user_uid' => $user->uid
            ]
        );

        // Verificar que el recurso educativo fue creado
        $this->assertNotNull($educationalResource, 'El recurso educativo no fue creado correctamente.');

        // Realizar la petición POST a la ruta, enviando la calificación y el UID del recurso
        $response = $this->post('/resource/calificate', [
            'calification' => 4,
            'educational_resource_uid' => $educationalResource->uid
        ]);

        // Verificar que la respuesta es 200 (OK)
        $response->assertStatus(200);

        // Verificar que el mensaje de éxito está presente en la respuesta JSON
        $response->assertJson([
            'message' => 'Se ha registrado correctamente la calificación',
        ]);

        // Verificar que la calificación se ha guardado correctamente en la base de datos
        $this->assertDatabaseHas('educational_resources_assessments', [
            'user_uid' => $user->uid,
            'educational_resources_uid' => $educationalResource->uid,
            'calification' => 4,
        ]);
    }
}
