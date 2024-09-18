<?php

namespace Tests\Unit;

use App\Models\DepartmentsModel;
use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\HeaderPagesModel;
use App\Models\GeneralOptionsModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Profile\MyProfileController;


class ProfileControllerTest extends TestCase
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
        $general_options = GeneralOptionsModel::all()->pluck('option_value', 'option_name')->toArray();
        app()->instance('general_options', $general_options);
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

    }
/**
 * @test Index
 */

    public function testIndexProfile()
    {
        // Arrange
        $user = UsersModel::factory()->create()->first();
        $this->actingAs($user);

        // Simula las notificaciones generales
        $generalNotifications = [
            [
                'uid' => generate_uuid(), // Asegúrate de que esto genere un UUID válido
                'type' => 'general_notification',
                'is_read' => false,
                'title' => 'Notificación de prueba',
                'description' => 'Esta es una descripción de prueba.',
                'date' => now(), // O cualquier fecha válida
            ],
            [
                'uid' => generate_uuid(),
                'type' => 'general_notification',
                'is_read' => true,
                'title' => 'Notificación leída',
                'description' => 'Descripción de una notificación leída.',
                'date' => now()->subDays(1), // Fecha anterior para simular una notificación leída
            ],
        ];

        // Establece las variables compartidas manualmente
        View::share('general_notifications', $generalNotifications);
        View::share('unread_general_notifications', true); // Cambia a false si no hay notificaciones no leídas


        // Act
        $response = $this->get('/profile/update_account');

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('profile.my_profile.index');
        $response->assertSee('Mi perfil'); // Adjust as needed based on expected view content
        $response->assertSee($user->name); // Adjust as needed based on expected user data
        $response->assertSee('resources/js/my_profile.js');
    }





}
