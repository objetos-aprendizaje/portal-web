<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Jobs\SendEmailJob;
use App\Models\UsersModel;
use App\Models\EmailVerifyModel;
use App\Models\Saml2TenantsModel;
use App\Models\GeneralOptionsModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterControllerTest extends TestCase
{

    use RefreshDatabase;




    /**
     * @test
     * Prueba que la vista de registro se carga con los datos correctos
     */
    public function testIndexLoadsRegisterPageWithCorrectData()
    {
        // Simular la configuración de parámetros de sistemas de login
        $parameters_login_systems = [
            'google_login_active' => true,
            'facebook_login_active' => true,
            'twitter_login_active' => true,
            'linkedin_login_active' => true,
            'cas_active' => true, // Añadir esta clave
            'rediris_active' => true, // Añadir esta clave
        ];
        Cache::shouldReceive('get')->with('parameters_login_systems')->andReturn($parameters_login_systems);

        // Simular la recuperación de la configuración de CAS y Rediris
        $casUrl = 'https://example.com/cas-login';
        $redirisUrl = 'https://example.com/rediris-login';

        $this->mock(Saml2TenantsModel::class, function ($mock) use ($casUrl, $redirisUrl) {
            $mock->shouldReceive('where->first->uuid')->andReturn('cas-uuid');
            $mock->shouldReceive('where->first->uuid')->andReturn('rediris-uuid');
        });

        // Simular la obtención del logotipo desde la base de datos
        GeneralOptionsModel::factory()->create([
            'option_name' => 'poa_logo_1',
            'option_value' => 'https://example.com/logo.png',
        ]);

        // Hacer la solicitud GET a la ruta de registro
        $response = $this->get(route('register'));

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que se carga la vista correcta
        $response->assertViewIs('non_authenticated.register');

        // Verificar que los datos pasados a la vista son correctos
        $response->assertViewHas('page_name', 'Regístrate');
        $response->assertViewHas('page_title', 'Regístrate');
        // $response->assertViewHas('logo', 'https://example.com/logo.png');
        $response->assertViewHas('resources', [
            'resources/js/register.js',
        ]);
        $response->assertViewHas('cert_login', env('DOMINIO_CERTIFICADO'));
        // $response->assertViewHas('urlCas', $casUrl);
        // $response->assertViewHas('urlRediris', $redirisUrl);
        $response->assertViewHas('parameters_login_systems', $parameters_login_systems);

        // Caso 2: Logotipo no existente en la base de datos
        GeneralOptionsModel::where('option_name','poa_logo_1' )->delete(); // Elimina el logotipo para simular que no existe

        GeneralOptionsModel::factory()->create([
            'option_name' => 'poa_logo_2',
            'option_value' => 'https://example.com/no-logo.png',
        ]);

        $response = $this->get(route('register'));

        
    }

    /**
     * @test
     * Prueba para reenviar la confirmación de email.
     */
    public function testResendEmailConfirmationUser()
    {
        // Crear un usuario no verificado en la base de datos
        $user = UsersModel::factory()->create([
            'email' => 'test@example.com',
            'verified' => false,
        ]);

        // Simular la cola de correos
        Queue::fake();

        // Hacer la solicitud POST para reenviar la confirmación de email
        $response = $this->post('/register/resend_email_confirmation', ['email' => 'test@example.com']);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que se ha enviado el email de confirmación      

        Queue::assertPushed(SendEmailJob::class, 1);

        // Verificar que el mensaje de éxito se incluye en la respuesta
        $response->assertJson([
            'message' => 'Se ha reenviado el email'
        ]);

        // Verificar que se ha generado y guardado un nuevo token de verificación
        // $this->assertDatabaseHas('email_verifies', [
        //     'user_uid' => $user->uid,
        // ]);
    }

    /**
     * @test
     * Prueba para error al reenviar confirmación de email cuando la cuenta no existe.
     */
    public function testResendEmailConfirmationFailsWhenUserNotFound()
    {
        // Hacer la solicitud POST con un correo que no existe en la base de datos
        $response = $this->post('/register/resend_email_confirmation', ['email' => 'nonexistent@example.com']);

        // Verificar que se lanza una excepción con el código de estado 404
        $response->assertStatus(404);

        // Verificar que el mensaje de error es el esperado
        $response->assertJson([
            'message' => 'No se ha encontrado ninguna cuenta con esa dirección de correo'
        ]);
    }

    /**
     * @test
     * Prueba para error al reenviar confirmación de email cuando la cuenta ya está verificada.
     */
    public function testResendEmailConfirmationFailsWhenUserAlreadyVerified()
    {
        // Crear un usuario verificado en la base de datos
        $user = UsersModel::factory()->create([
            'email' => 'test@example.com',
            'verified' => true,
        ]);

        // Hacer la solicitud POST para reenviar la confirmación de email
        $response = $this->post('/register/resend_email_confirmation', ['email' => 'test@example.com']);

        // Verificar que se lanza una excepción con el código de estado 405
        $response->assertStatus(405);

        // Verificar que el mensaje de error es el esperado
        $response->assertJson([
            'message' => 'Su cuenta ya está verificada'
        ]);
    }

    /**
     * @test
     * Prueba de registro de usuario
     */
    public function testSubmitRegistersUserAndSendsEmailVerification()
    {
        // Simular la cola de correos
        Queue::fake();

        // Datos de solicitud simulada
        $requestData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        // Hacer la solicitud POST a la ruta de registro
        $response = $this->post(route('registerUser'), $requestData);

        // Verificar que el usuario fue creado en la base de datos
        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        // Verificar que el rol de estudiante fue asignado al usuario
        $user = UsersModel::where('email', 'johndoe@example.com')->first();
        $this->assertTrue($user->roles()->where('code', 'STUDENT')->exists());

        // Verificar que se ha enviado un correo de verificación
        Queue::assertPushed(SendEmailJob::class, 1);
        // Queue::assertPushed(SendEmailJob::class, function ($job) use ($user) {
        //     return $job->email === $user->email;
        // });

        // Verificar que la respuesta redirige correctamente a la página de inicio de sesión
        $response->assertRedirect('/login');

        // Verificar los datos de la sesión de manera manual
        $sessionData = session()->all();
        $this->assertArrayHasKey('account_created', $sessionData, 'La clave "account_created" no se encuentra en la sesión.');
        $this->assertEquals('johndoe@example.com', $sessionData['account_created']);
        $this->assertArrayHasKey('email', $sessionData, 'La clave "email" no se encuentra en la sesión.');
        $this->assertEquals('johndoe@example.com', $sessionData['email']);
    }

    /**
     * @test
     * Prueba de verificación de correo electrónico
     */
    public function testVerifyEmail()
    {
        // Crear un usuario en la base de datos
        UsersModel::factory()->create([
            'verified' => false,
            'email' => "prueba@prueba.com",
        ]);

        $user = UsersModel::where('email', "prueba@prueba.com")->first();

        // Crear un registro de verificación de correo electrónico en la base de datos
        $verify = EmailVerifyModel::factory()->create([
            'user_uid' => $user->uid,
            'token' => 'valid-token',
            'expires_at' => now()->addMinutes(30),
        ]);

        // Hacer la solicitud GET a la ruta de verificación de correo electrónico
        $response = $this->get(route('verification.verify', ['token' => 'valid-token']));


        // Recargar la instancia del usuario desde la base de datos para obtener los cambios
        $user->refresh();

        // Verificar que el usuario ha sido marcado como verificado en la base de datos
        $this->assertEquals(1, $user->verified);

        // Verificar que el registro de verificación ha sido eliminado de la base de datos
        $this->assertDatabaseMissing('email_verification_tokens', [
            'token' => 'valid-token',
            'expires_at' => now()->addMinutes(30),
        ]);

        // Verificar que la respuesta redirige correctamente a la página de inicio de sesión
        $response->assertRedirect('/login');

        // Verificar los datos de la sesión de manera manual
        $sessionData = session()->all();
        $this->assertArrayHasKey('email_verified', $sessionData, 'La clave "email_verified" no se encuentra en la sesión.');
        $this->assertEquals($user->email, $sessionData['email_verified']);
    }

    /**
     * @test
     * Prueba de verificación de correo electrónico con token expirado
     */
    public function testVerifyEmailWithExpiredToken()
    {
        // Crear un usuario en la base de datos
        UsersModel::factory()->create([
            'verified' => false,
            'email' => "prueba@prueba.com",
        ]);

        $user = UsersModel::where('email', "prueba@prueba.com")->first();

        // Crear un registro de verificación de correo electrónico en la base de datos con un token expirado
        $verify = EmailVerifyModel::factory()->create([
            'user_uid' => $user->uid,
            'token' => 'expired-token',
            'expires_at' => now()->subMinutes(30),
        ]);

        // Hacer la solicitud GET a la ruta de verificación de correo electrónico con un token expirado
        $response = $this->get(route('verification.verify', ['token' => 'expired-token']));

        // Verificar que el usuario no ha sido marcado como verificado en la base de datos     
        $this->assertEquals(0, $user->verified);

        // Verificar que el registro de verificación todavía existe en la base de datos
        $this->assertDatabaseHas('email_verification_tokens', [
            'token' => 'expired-token',
        ]);

        // Verificar que la respuesta redirige correctamente a la página de inicio de sesión con un mensaje de error
        $response->assertRedirect('/login');

        // Verificar los datos de la sesión de manera manual
        $sessionData = session()->all();
        $this->assertArrayHasKey('verify_link_expired', $sessionData, 'La clave "verify_link_expired" no se encuentra en la sesión.');
        $this->assertTrue($sessionData['verify_link_expired']);
        $this->assertArrayHasKey('email', $sessionData, 'La clave "email" no se encuentra en la sesión.');
        $this->assertEquals($user->email, $sessionData['email']);
    }

    /**
     * @test
     * Prueba de verificación de correo electrónico con token inválido
     */
    public function testVerifyEmailWithInvalidToken()
    {
        // Hacer la solicitud GET a la ruta de verificación de correo electrónico con un token inválido
        $response = $this->get(route('verification.verify', ['token' => 'invalid-token']));

        // Verificar que la respuesta redirige correctamente a la página de error
        $response->assertRedirect('/error/002');
    }
}
