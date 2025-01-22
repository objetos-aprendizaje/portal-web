<?php

namespace Tests\Unit;

use App\Models\GeneralOptionsModel;
use Tests\TestCase;
use App\Models\UsersModel;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetEmailControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $this->assertTrue(true);
    }

    public function testIndexGetEmail()
    {

        $general_options  = [
            'redsys_url' => 'http://midominio.com',
            'color_1' => '#333333',
            'color_2' => '#222',
            'color_3' => '#111',
            'color_4' => '#000',
            'scripts' => '<script src="jquery.js"></script>',
            'poa_logo_1' => 'image1.png',
            'poa_logo_2' => 'image2.png',
            'poa_logo_3' => 'image3.png',
            'registration_active' => false,
        ];

        app()->instance('general_options', $general_options);

        View::share('general_options', $general_options);

        // Realizar la llamada a la ruta
        $response = $this->get(route('get-email'));

        $response->assertViewIs('non_authenticated.get_email');
    }


    // Todo: Solventar error de envio de correo 
    /**
     * Test que verifica la actualización del correo electrónico y la redirección.
     *
     */
    // public function testAddUserEmail()
    // {
    //     // Crear un usuario de prueba
    //     $user = UsersModel::factory()->create([
    //         'nif' => '12345678A',
    //         'identity_verified' => false,
    //     ]);

    //     GeneralOptionsModel::factory()->create(
    //         [
    //             'option_name' => 'commercial_name',
    //             'option_value' => 'Pruebas'
    //         ]

    //     );

    //     $general_options  = [
    //         'commercial_name' => 'Pruebas',
    //     ];

    //     app()->instance('general_options', $general_options);

    //     View::share('general_options', $general_options);

    //     // Simular la sesión
    //     Session::put('dataCertificate', [
    //         'nif' => $user->nif,
    //     ]);

    //     // Datos del formulario
    //     $data = [
    //         'email' => 'test@example.com',
    //         'email_verification' => 'test@example.com',
    //     ];

    //     // Realizar la petición POST
    //     $response = $this->post(route('add-user'), $data);

    //     // Verificar que se redirija al login
    //     $response->assertRedirect('/login');

    //     // Verificar que el correo electrónico se actualizó correctamente
    //     $updatedUser = UsersModel::where('nif', $user->nif)->first();
    //     $this->assertEquals($data['email'], $updatedUser->email);
    //     $this->assertTrue($updatedUser->identity_verified);

    //     // Verificar que la sesión se eliminó
    //     $this->assertNull(Session::get('dataCertificate'));
    // }
}
