<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\UserRolesModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CertificateAccessControllerTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use RefreshDatabase;

    /**
     * Test de inicio de sesión con certificado válido.
     *
     * @return void
     */
    public function test_login_certificate_valid()
    {
        UsersModel::factory()->create([
            'nif' => '12345678A',
            'first_name' => 'Pedro',
            'last_name' => 'Perez',
        ]);

        // Configura los datos para la prueba
        $data = json_encode([
            'nif' => '12345678A',
            'first_name' => 'Pedro',
            'last_name' => 'Perez',
        ]);

        $expiration = time() + 3600; // Expira en una hora
        $hash = ($data . $expiration . env('KEY_CHECK_CERTIFICATE_ACCESS'));
        
        // Envía la solicitud GET
        $response = $this->get('/login/certificate?data=' . $data . '&expiration=' . $expiration . '&hash=' . $hash);

        // Verifica que la respuesta sea una redirección
        $response->assertRedirect();
    }
}
