<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Jobs\SendEmailJob;
use App\Models\UsersModel;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\RecoverPasswordController;

class RecoverPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Prueba que la vista de restablecer contraseña se carga con los datos correctos
     */
    public function testIndexLoadsRecoverPasswordPageWithCorrectData()
    {
        // Hacer la solicitud GET a la ruta de restablecimiento de contraseña
        $response = $this->get(route('recover-password'));

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que se carga la vista correcta
        $response->assertViewIs('non_authenticated.recover_password');

        // Verificar que los datos pasados a la vista son correctos
        $response->assertViewHas('page_name', 'Restablecer contraseña');
        $response->assertViewHas('page_title', 'Restablecer contraseña');
        $response->assertViewHas('resources', [
            'resources/js/recover_password.js',
        ]);
    }

    /**
     * @test
     * Prueba que verifica el método privado sendEmailRecoverPassword
     * asegurándose de que el token se genera, se almacena en la base de datos y se envía un correo.
     */
    public function testSendEmailRecoverPasswordGeneratesTokenAndSendsEmail()
    {
        // Crear un usuario en la base de datos
        $user = UsersModel::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Simular la cola de correos
        Queue::fake();

        // Llamar al método privado sendEmailRecoverPassword
        $reflection = new \ReflectionClass(RecoverPasswordController::class);
        $method = $reflection->getMethod('sendEmailRecoverPassword');
        $method->setAccessible(true);
        $controller = new RecoverPasswordController();
        $method->invoke($controller, $user);

        // Verificar que el token se ha almacenado en la base de datos
        $this->assertDatabaseHas('reset_password_tokens', [
            'uid_user' => $user->uid,
            'email' => 'test@example.com',
            // No verificamos el token en sí, pero aseguramos que esté presente
            'expiration_date' => now()->addMinutes(env('PWRES_TOKEN_EXPIRATION_MIN', 60)),
        ]);

        // Verificar que se ha enviado el correo de restablecimiento de contraseña
        Queue::assertPushed(SendEmailJob::class, 1);
    }

    /**
     * @test
     * Prueba que verifica que se envía un email de recuperación de contraseña si el usuario existe.
     */
    public function testRecoverPasswordSendsEmailIfUserExists()
    {
        // Crear un usuario en la base de datos
        $user = UsersModel::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Simular la cola de correos
        Queue::fake();

        // Simular la solicitud POST con el email del usuario
        $response = $this->post('/recover_password/send', [
            'email' => 'test@example.com',
        ]);

        // Verificar que se ha enviado el correo de recuperación de contraseña
        Queue::assertPushed(SendEmailJob::class,1);

        // Verificar que la redirección es a la página de login
        $response->assertRedirect(route('login'));

        // Verificar que el mensaje de éxito está presente en la sesión
        $response->assertSessionHas('success', ['Se ha enviado un email para reestablecer la contraseña']);
    }

    /**
     * @test
     * Prueba que verifica que no se envía un email si el usuario no existe.
     */
    public function testRecoverPasswordDoesNotSendEmailIfUserDoesNotExist()
    {
        // Simular la cola de correos
        Queue::fake();

        // Simular la solicitud POST con un email que no existe en la base de datos
        $response = $this->post('/recover_password/send', [
            'email' => 'nonexistent@example.com',
        ]);

        // Verificar que no se ha enviado ningún correo
        Queue::assertNothingPushed();

        // Verificar que la redirección es a la página de login
        $response->assertRedirect(route('login'));

        // Verificar que el mensaje de éxito está presente en la sesión
        $response->assertSessionHas('success', ['Se ha enviado un email para reestablecer la contraseña']);
    }
}
