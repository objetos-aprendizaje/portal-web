<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsersModel;
use Illuminate\Support\Facades\Hash;
use App\Models\ResetPasswordTokensModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResetPasswordControllerTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     * Prueba que verifica que la vista de restablecimiento de contraseña se carga correctamente con el token y el correo electrónico.
     */
    public function testResetPasswordPageLoadsCorrectly()
    {
        // Simular el token y el email
        $token = 'valid-token';
        $email = 'test@example.com';

        // Hacer la solicitud GET a la ruta de restablecimiento de contraseña con el token y el email
        $response = $this->get(route('reset-password', ['token' => $token, 'email' => $email]));

        // Verificar que la vista se carga correctamente
        $response->assertStatus(200);

        // Verificar que la vista correcta se ha cargado
        $response->assertViewIs('non_authenticated.reset_password');

        // Verificar que los datos pasados a la vista son correctos
        $response->assertViewHas('token', $token);
        $response->assertViewHas('email', $email);
    }

    /**
     * @test
     * Prueba que verifica que el restablecimiento de contraseña se realiza correctamente.
     */
    public function testResetPasswordSuccessfully()
    {
        // Crear un usuario en la base de datos
        $user = UsersModel::factory()->create([
            'password' => password_hash('old_password', PASSWORD_BCRYPT),
        ]);

        // Crear un token de restablecimiento de contraseña en la base de datos
        $resetPasswordToken = ResetPasswordTokensModel::factory()->create([
            'uid_user' => $user->uid,
            'token' => 'valid-token',
            'expiration_date' => now()->addMinutes(30),
        ]);

        // Simular los datos de la solicitud de restablecimiento de contraseña
        $requestData = [
            'token' => 'valid-token',
            'password' => 'new_password123',
            'expiration_date' => now()->addMinutes(30),
        ];

        // Hacer la solicitud POST a la ruta de restablecimiento de contraseña
        $response = $this->post('/reset_password/send', $requestData);

        // Verificar que la contraseña del usuario ha sido actualizada en la base de datos
        $this->assertTrue(Hash::check('new_password123', $user->fresh()->password));

        // Verificar que el token de restablecimiento de contraseña ha sido eliminado
        // $this->assertDatabaseMissing('reset_password_tokens', [
        //     'token' => 'valid-token',
        // ]);

        // Verificar que la respuesta redirige correctamente a la página de inicio de sesión
        $response->assertRedirect(route('login'));

        // Verificar los datos de la sesión
        $sessionData = session()->all();
        $this->assertArrayHasKey('success', $sessionData, 'La clave "success" no se encuentra en la sesión.');
        $this->assertEquals(['Se ha restablecido la contraseña'], $sessionData['success']);
    }


    /**
     * @test
     * Prueba que verifica que el restablecimiento de contraseña falla cuando el token es inválido.
     */
    public function testResetPasswordFailsWithInvalidToken()
    {
        // Simular los datos de la solicitud de restablecimiento de contraseña
        $requestData = [
            'token' => 'invalid-token',
            'password' => 'new_password123',
        ];

        // Hacer la solicitud POST a la ruta de restablecimiento de contraseña
        $response = $this->post('/reset_password/send', $requestData);

        // Verificar que la respuesta redirige a la página de inicio de sesión con un error
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('reset', false);
        $response->assertSessionHas('message', 'El token no es válido');
    }

    /**
     * @test
     * Prueba que verifica que el restablecimiento de contraseña falla cuando la contraseña no cumple con los requisitos mínimos.
     */
    public function testResetPasswordFailsWithInvalidPassword()
    {

        // Crear un usuario en la base de datos
         $user = UsersModel::factory()->create([
            'password' => password_hash('old_password', PASSWORD_BCRYPT),
        ]);


        // Crear un token de restablecimiento de contraseña válido
        $resetPasswordToken = ResetPasswordTokensModel::factory()->create([
            'uid_user' => $user->uid,
            'token' => 'valid-token',
            'expiration_date' => now()->addMinutes(30),
        ]);

        // Simular los datos de la solicitud de restablecimiento de contraseña con una contraseña inválida
        $requestData = [
            'token' => 'valid-token',
            'password' => 'short',
        ];

        // Hacer la solicitud POST a la ruta de restablecimiento de contraseña
        $response = $this->post('/reset_password/send', $requestData);

        // Verificar que la respuesta redirige a la misma página con un error de validación
        $response->assertRedirect();
        $response->assertSessionHasErrors(['password']);
    }
}
