<?php

namespace Tests\Unit;

use Mockery;
use stdClass;
use Tests\TestCase;
use GuzzleHttp\Client;
use App\Models\UsersModel;
use App\Models\UserRolesModel;
use Illuminate\Support\Carbon;
use App\Models\Saml2TenantsModel;
use App\Models\UsersAccessesModel;
use App\Models\GeneralOptionsModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\LoginController;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Foundation\Testing\RefreshDatabase;


class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Prueba que el usuario cierre sesión y se redirija a la página principal
     */
    public function testLogoutRedirectsToHomeWithoutGoogleSession()
    {
        // Buscamos un usuario
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        // Si no existe el usuario lo creamos
        if (!$user) {
            $user = UsersModel::factory()->create([
                'email' => 'admin@admin.com'
            ])->first();
        }
        // Lo autenticarlo
        $this->actingAs($user);

        // Hacer la solicitud GET a la ruta de logout
        $response = $this->get('/logout');

        // Verificar que la sesión se vacíe y el usuario cierre sesión
        $this->assertGuest();

        // Verificar que la redirección sea a la página principal
        $response->assertRedirect('/');
    }

    /**
     * @test
     * Prueba que el token de Google se revoque si existe en la sesión y el usuario cierre sesión
     */
    public function testLogoutRevokesGoogleTokenIfPresent()
    {
        // Buscamos un usuario
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        // Si no existe el usuario lo creamos
        if (!$user) {
            $user = UsersModel::factory()->create([
                'email',
                'admin@admin.com'
            ])->first();
        }
        // Lo autenticarlo
        $this->actingAs($user);

        // Simular los datos de sesión de Google
        Session::put('google_id', 'google-id');
        Session::put('token_google', 'test-token');

        // Hacer la solicitud GET a la ruta de logout
        $response = $this->get('/logout');

        // Verificar que el usuario ha sido deslogueado
        $this->assertFalse(Auth::check());

        // Verificar que la sesión ha sido limpiada
        $this->assertNull(Session::get('google_id'));
        $this->assertNull(Session::get('token_google'));


        // Verificar que la sesión se vacíe y el usuario cierre sesión
        $this->assertGuest();

        // Verificar que la redirección sea a la página principal
        $response->assertRedirect('/');
    }

    /**
     * @test
     * Prueba que la vista de inicio de sesión se carga correctamente con el logo desde la base de datos
     */
    public function testIndexLoadsLoginPageWithLogo()
    {
        // Crear una opción general en la base de datos para el logo
        $logoBd = GeneralOptionsModel::where('option_name', 'poa_logo_1')->first();

        if($logoBd){
            $logoBd->option_value = 'logo.png';
            $logoBd->save();
        }else{
            GeneralOptionsModel::factory()->create([
                'option_name' => 'poa_logo_1',
                'option_value' => 'logo.png'
            ]);
        }
        // Crear las opciones necesarias para los sistemas de login
        GeneralOptionsModel::factory()->create(['option_name' => 'google_login_active', 'option_value' => '1']);
        GeneralOptionsModel::factory()->create(['option_name' => 'google_client_id', 'option_value' => 'google-client-id']);
        GeneralOptionsModel::factory()->create(['option_name' => 'google_client_secret', 'option_value' => 'google-client-secret']);
        GeneralOptionsModel::factory()->create(['option_name' => 'facebook_login_active', 'option_value' => '1']);
        GeneralOptionsModel::factory()->create(['option_name' => 'facebook_client_id', 'option_value' => 'facebook-client-id']);
        GeneralOptionsModel::factory()->create(['option_name' => 'facebook_client_secret', 'option_value' => 'facebook-client-secret']);
        GeneralOptionsModel::factory()->create(['option_name' => 'twitter_login_active', 'option_value' => '1']);
        GeneralOptionsModel::factory()->create(['option_name' => 'twitter_client_id', 'option_value' => 'twitter-client-id']);
        GeneralOptionsModel::factory()->create(['option_name' => 'twitter_client_secret', 'option_value' => 'twitter-client-secret']);
        GeneralOptionsModel::factory()->create(['option_name' => 'linkedin_login_active', 'option_value' => '1']);
        GeneralOptionsModel::factory()->create(['option_name' => 'linkedin_client_id', 'option_value' => 'linkedin-client-id']);
        GeneralOptionsModel::factory()->create(['option_name' => 'linkedin_client_secret', 'option_value' => 'linkedin-client-secret']);

        // Simular la caché de los parámetros del sistema de login
        Cache::shouldReceive('get')
            ->with('parameters_login_systems')
            ->andReturn([
                'google_login_active' => '1',
                'google_client_id' => 'google-client-id',
                'google_client_secret' => 'google-client-secret',
                'facebook_login_active' => '1',
                'facebook_client_id' => 'facebook-client-id',
                'facebook_client_secret' => 'facebook-client-secret',
                'twitter_login_active' => '1',
                'twitter_client_id' => 'twitter-client-id',
                'twitter_client_secret' => 'twitter-client-secret',
                'linkedin_login_active' => '1',
                'linkedin_client_id' => 'linkedin-client-id',
                'linkedin_client_secret' => 'linkedin-client-secret'
            ]);

        // Crear los registros simulados para CAS y Rediris
        Saml2TenantsModel::factory()->create(['key' => 'cas', 'uuid' => "50440107-2cdd-488f-a6f3-f8082921f715"]);
        Saml2TenantsModel::factory()->create(['key' => 'rediris', 'uuid' => "50440107-2cdd-488f-a6f3-f8082921f720"]);

        // Hacer la solicitud GET a la ruta de login
        $response = $this->get(route('login'));

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que la vista correcta se cargue
        $response->assertViewIs('non_authenticated.login');

        // Verificar que los parámetros de los sistemas de login se pasen correctamente
        $response->assertViewHas('parameters_login_systems', [
            'google_login_active' => '1',
            'google_client_id' => 'google-client-id',
            'google_client_secret' => 'google-client-secret',
            'facebook_login_active' => '1',
            'facebook_client_id' => 'facebook-client-id',
            'facebook_client_secret' => 'facebook-client-secret',
            'twitter_login_active' => '1',
            'twitter_client_id' => 'twitter-client-id',
            'twitter_client_secret' => 'twitter-client-secret',
            'linkedin_login_active' => '1',
            'linkedin_client_id' => 'linkedin-client-id',
            'linkedin_client_secret' => 'linkedin-client-secret'
        ]);
    }

    /**
     * @test
     * Prueba que la vista de inicio de sesión se carga correctamente con el logo desde la base de datos
     */
    public function testIndexLoadsLoginPageWithoutLogo()
    {
        $cas = GeneralOptionsModel::where('option_name', 'cas_active')->first();

        if($cas){
            $cas->option_value = true;
            $cas->save();
        }else{
            GeneralOptionsModel::factory()->create([
                'option_name' => 'cas_active',
                'option_value' => true,
            ]);
        }

        $rediris = GeneralOptionsModel::where('option_name', 'rediris_active')->first();

        if($rediris){
            $rediris->option_value = true;
            $rediris->save();
        }else{
            GeneralOptionsModel::factory()->create([
                'option_name' => 'rediris_active',
                'option_value' => true,
            ]);
        }
       
        // Crear las opciones necesarias para los sistemas de login
        GeneralOptionsModel::factory()->create(['option_name' => 'google_login_active', 'option_value' => '1']);
        GeneralOptionsModel::factory()->create(['option_name' => 'google_client_id', 'option_value' => 'google-client-id']);
        GeneralOptionsModel::factory()->create(['option_name' => 'google_client_secret', 'option_value' => 'google-client-secret']);
        GeneralOptionsModel::factory()->create(['option_name' => 'facebook_login_active', 'option_value' => '1']);
        GeneralOptionsModel::factory()->create(['option_name' => 'facebook_client_id', 'option_value' => 'facebook-client-id']);
        GeneralOptionsModel::factory()->create(['option_name' => 'facebook_client_secret', 'option_value' => 'facebook-client-secret']);
        GeneralOptionsModel::factory()->create(['option_name' => 'twitter_login_active', 'option_value' => '1']);
        GeneralOptionsModel::factory()->create(['option_name' => 'twitter_client_id', 'option_value' => 'twitter-client-id']);
        GeneralOptionsModel::factory()->create(['option_name' => 'twitter_client_secret', 'option_value' => 'twitter-client-secret']);
        GeneralOptionsModel::factory()->create(['option_name' => 'linkedin_login_active', 'option_value' => '1']);
        GeneralOptionsModel::factory()->create(['option_name' => 'linkedin_client_id', 'option_value' => 'linkedin-client-id']);
        GeneralOptionsModel::factory()->create(['option_name' => 'linkedin_client_secret', 'option_value' => 'linkedin-client-secret']);

        // Simular la caché de los parámetros del sistema de login
        Cache::shouldReceive('get')
            ->with('parameters_login_systems')
            ->andReturn([
                'google_login_active' => '1',
                'google_client_id' => 'google-client-id',
                'google_client_secret' => 'google-client-secret',
                'facebook_login_active' => '1',
                'facebook_client_id' => 'facebook-client-id',
                'facebook_client_secret' => 'facebook-client-secret',
                'twitter_login_active' => '1',
                'twitter_client_id' => 'twitter-client-id',
                'twitter_client_secret' => 'twitter-client-secret',
                'linkedin_login_active' => '1',
                'linkedin_client_id' => 'linkedin-client-id',
                'linkedin_client_secret' => 'linkedin-client-secret'
            ]);

        // Crear los registros simulados para CAS y Rediris
        Saml2TenantsModel::factory()->create(['key' => 'cas', 'uuid' => "50440107-2cdd-488f-a6f3-f8082921f715"]);
        Saml2TenantsModel::factory()->create(['key' => 'rediris', 'uuid' => "50440107-2cdd-488f-a6f3-f8082921f720"]);

        // Hacer la solicitud GET a la ruta de login
        $response = $this->get(route('login'));

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que la vista correcta se cargue
        $response->assertViewIs('non_authenticated.login');

        // Verificar que los parámetros de los sistemas de login se pasen correctamente
        $response->assertViewHas('parameters_login_systems', [
            'google_login_active' => '1',
            'google_client_id' => 'google-client-id',
            'google_client_secret' => 'google-client-secret',
            'facebook_login_active' => '1',
            'facebook_client_id' => 'facebook-client-id',
            'facebook_client_secret' => 'facebook-client-secret',
            'twitter_login_active' => '1',
            'twitter_client_id' => 'twitter-client-id',
            'twitter_client_secret' => 'twitter-client-secret',
            'linkedin_login_active' => '1',
            'linkedin_client_id' => 'linkedin-client-id',
            'linkedin_client_secret' => 'linkedin-client-secret'
        ]);
    }

    /**
     * @test
     * Prueba la autenticación exitosa
     */
    public function testAuthenticateSuccessful()
    {
        // Crear un usuario verificado en la base de datos
        UsersModel::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'verified' => true
        ]);

        // Hacer una solicitud POST con credenciales válidas
        $response = $this->post('/login/authenticate', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Verificar que el usuario está autenticado
        $this->assertTrue(Auth::check());

        // Verificar la redirección a la URL por defecto
        $response->assertRedirect('/');
    }

    /**
     * @test
     * Prueba que se redirige al login si el usuario no es encontrado
     */
    public function testAuthenticateUserNotFound()
    {
        // Hacer una solicitud POST con credenciales no válidas
        $response = $this->post('/login/authenticate', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        // Verificar que el usuario no está autenticado
        $this->assertFalse(Auth::check());

        // Verificar la redirección al login con el mensaje de error
        $response->assertRedirect('/login');
        $response->assertSessionHas('user_not_found', true);
    }

    /**
     * @test
     * Prueba que se redirige al login si el usuario no está verificado
     */
    public function testAuthenticateUserNotVerified()
    {
        // Crear un usuario no verificado en la base de datos
        UsersModel::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'verified' => false
        ]);

        // Hacer una solicitud POST con credenciales válidas
        $response = $this->post('/login/authenticate', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Verificar que el usuario no está autenticado
        $this->assertFalse(Auth::check());

        // Verificar la redirección al login con el mensaje de no verificado
        $response->assertRedirect('/login');
        $response->assertSessionHas('user_not_verified', true);
        $response->assertSessionHas('email', 'test@example.com');
    }

    /**
     * @test
     * Prueba que se redirige a una URL guardada en la sesión después del login
     */
    public function testAuthenticateRedirectsToStoredUrl()
    {
        // Crear un usuario verificado en la base de datos
        UsersModel::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'verified' => true
        ]);

        // Simular que hay una URL almacenada en la sesión
        session(['url.current' => '/profile']);

        // Hacer una solicitud POST con credenciales válidas
        $response = $this->post('/login/authenticate', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Verificar que el usuario está autenticado
        $this->assertTrue(Auth::check());

        // Verificar la redirección a la URL almacenada en la sesión
        $response->assertRedirect('/profile');
    }

    /**
     * @test
     * Prueba que el callback de Google maneja la autenticación correctamente
     *
     */
    public function testHandleGoogleCallbackAuthenticatesUser()
    {
        // Simular la respuesta de Google usando Mockery
        $user_google_mock = Mockery::mock(\Laravel\Socialite\Contracts\User::class);
        $user_google_mock->shouldReceive('getEmail')->andReturn('test@example.com');
        $user_google_mock->email = 'test@example.com';
        $user_google_mock->user = [
            'given_name' => 'John',
            'family_name' => 'Doe'
        ];
        $user_google_mock->shouldReceive('getId')->andReturn('google-id-123');
        $user_google_mock->id = 'google-id-123';
        $user_google_mock->shouldReceive('token')->andReturn('google-token-xyz');

        $user_google_mock->token = 'google-token-xyz';

        // Simular la autenticación de Google usando Socialite
        Socialite::shouldReceive('driver->user')
            ->andReturn($user_google_mock);

        // Simular que el rol de estudiante ya existe
        UserRolesModel::factory()->create([
            'code' => 'STUDENT',
        ]);

        // Hacer la solicitud a la ruta del callback de Google
        $response = $this->get('/auth/callback/google');

        // Verificar que el usuario tiene el rol de estudiante asignado
        // Buscamos un usuario
        $user = UsersModel::where('email', 'test@example.com')->first();
        // Si no existe el usuario lo creamos
        if (!$user) {
            $user = UsersModel::factory()->create([
                'email' => 'test@example.com',
                "first_name" => "John",
                "last_name" => "Doe"
            ])->first();
        }
        // Verificar que el usuario fue creado correctamente en la base de datos

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        $this->actingAs($user);

        $this->assertTrue($user->roles()->where('code', 'STUDENT')->exists());

        // Verificar que el usuario está autenticado correctamente
        $this->assertTrue(Auth::check());

        // Verificar que la respuesta redirige correctamente a la página principal
        $response->assertRedirect('/');
    }

    /**
     * @test
     * Prueba que el método redirectToGoogle redirige correctamente a la página de autenticación de Google
     */
    public function testRedirectToGoogle()
    {
        // Simular el redireccionamiento de Google usando Mockery
        $socialite_mock = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $socialite_mock->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        // Simular el driver de Socialite
        Socialite::shouldReceive('driver')->with('google')->andReturn($socialite_mock);

        // Hacer la solicitud a la ruta que redirige a Google
        $response = $this->get('/auth/google');

        // Verificar que la redirección es correcta
        $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    /**
     * @test
     * Prueba que el método redirectToTwitter redirige correctamente a la página de autenticación de Twitter
     */
    public function testRedirectToTwitter()
    {
        // Simular el redireccionamiento de Twitter usando Mockery
        $socialite_mock = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $socialite_mock->shouldReceive('redirect')->andReturn(redirect('https://api.twitter.com/oauth/authenticate'));

        // Simular el driver de Socialite
        Socialite::shouldReceive('driver')->with('twitter')->andReturn($socialite_mock);

        // Hacer la solicitud a la ruta que redirige a Twitter
        $response = $this->get('/auth/twitter');

        // Verificar que la redirección es correcta
        $response->assertRedirect('https://api.twitter.com/oauth/authenticate');
    }

    /**
     * @test
     * Prueba que el callback de Twitter maneja la autenticación correctamente
     */
    public function testHandleTwitterCallbackAuthenticatesUser()
    {
        // Simular la respuesta de Twitter usando Mockery
        $user_twitter_mock = Mockery::mock(\Laravel\Socialite\Contracts\User::class);
        $user_twitter_mock->email = 'test@example.com';
        $user_twitter_mock->id = 'twitter-id-123';
        $user_twitter_mock->token = 'twitter-token-xyz';
        $user_twitter_mock->name = 'John Doe';

        // Simular la autenticación de Twitter usando Socialite
        Socialite::shouldReceive('driver')->with('twitter')->andReturnSelf();
        Socialite::shouldReceive('user')->andReturn($user_twitter_mock);

        // Hacer la solicitud a la ruta del callback de Twitter
        $response = $this->get('/auth/callback/twitter');

        // Verificar que el usuario tiene el rol de estudiante asignado

        // Buscamos un usuario
        $user = UsersModel::where('email', 'test@example.com')->first();
        // Si no existe el usuario lo creamos
        if (!$user) {
            $user = UsersModel::factory()->create([
                'email' => 'test@example.com',
                "first_name" => "John Doe",
            ])->first();
        }

        // Verificar que el usuario fue creado correctamente en la base de datos
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'first_name' => 'John Doe',
        ]);

        $roles = UserRolesModel::firstOrCreate(['code' => 'STUDENT'], ['uid' => generate_uuid()]); // Crea roles de prueba
        $user->roles()->attach($roles->uid, ['uid' => generate_uuid()]);

        $this->actingAs($user);

        $this->assertTrue($user->roles()->where('code', 'STUDENT')->exists());

        // Verificar que el usuario está autenticado correctamente
        $this->assertTrue(Auth::check());

        // Verificar que la respuesta redirige correctamente a la página principal
        $response->assertRedirect('/');
    }

    public function testHandleTwitterCallbackAuthenticatesUserWithoutEmail()
    {
        // Simular la respuesta de Twitter usando Mockery
        $user_twitter_mock = Mockery::mock(\Laravel\Socialite\Contracts\User::class);
        $user_twitter_mock->email = '';
        $user_twitter_mock->id = 'twitter-id-123';
        $user_twitter_mock->token = 'twitter-token-xyz';
        $user_twitter_mock->name = 'John Doe';

        // Simular la autenticación de Twitter usando Socialite
        Socialite::shouldReceive('driver')->with('twitter')->andReturnSelf();
        Socialite::shouldReceive('user')->andReturn($user_twitter_mock);

        // Hacer la solicitud a la ruta del callback de Twitter
        $response = $this->get('/auth/callback/twitter');

        // Verificar que la respuesta redirige correctamente a la página principal
        $response->assertRedirect('/login');

        $response->assertRedirect(route('login'));

        // Verificar los datos de la sesión
        $sessionData = session()->all();
        $this->assertArrayHasKey('errors', $sessionData, 'No se ha podido obtener el email del usuario del método elegido');
       
    }


    public function testRedirectToLinkedin()
    {
        // Mock de Socialite para LinkedIn
        Socialite::shouldReceive('driver')
            ->with('linkedin-openid')
            ->andReturnSelf();

        Socialite::shouldReceive('redirect')
            ->andReturn(redirect('https://www.linkedin.com/oauth/v2/authorization'));

        // Hacer la solicitud a la ruta de redireccionamiento de LinkedIn
        $response = $this->get('/auth/linkedin-openid');

        // Verificar que la respuesta es una redirección a LinkedIn
        $response->assertRedirect('https://www.linkedin.com/oauth/v2/authorization');
    }

    /**
     * @test
     * Prueba que el callback de LinkedIn maneja la autenticación correctamente
     */
    public function testHandleLinkedinCallbackAuthenticatesUser()
    {
        // Limpiar usuarios de la base de datos antes de la prueba
        UsersModel::factory()->create([
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        // Simular la respuesta de LinkedIn usando Mockery
        $user_linkedin_mock = Mockery::mock(\Laravel\Socialite\Contracts\User::class);
        $user_linkedin_mock->email = 'test@example.com';
        $user_linkedin_mock->user = [
            'given_name' => 'John',
            'family_name' => 'Doe'
        ];
        $user_linkedin_mock->shouldReceive('getEmail')->andReturn('test@example.com');
        $user_linkedin_mock->shouldReceive('getId')->andReturn('linkedin-id-123');
        $user_linkedin_mock->shouldReceive('getName')->andReturn('John');
        $user_linkedin_mock->shouldReceive('getToken')->andReturn('test-token');

        // Simular la autenticación de LinkedIn usando Socialite
        Socialite::shouldReceive('driver')->with('linkedin-openid')->andReturnSelf();
        Socialite::shouldReceive('user')->andReturn($user_linkedin_mock);

        // Hacer la solicitud a la ruta del callback de LinkedIn
        $this->get('/auth/callback/linkedin-openid');

        // Verificar que el usuario fue creado correctamente en la base de datos
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        $student_role = UserRolesModel::where('code', 'STUDENT')->first();

        // Verificar que el usuario tiene el rol de estudiante asignado
        $user = UsersModel::where('email', 'test@example.com')->first();
        $this->actingAs($user);

        // Asignar el rol de estudiante al usuario, si no lo tiene
        if (!$user->roles()->where('code', 'STUDENT')->exists()) {
            $user->roles()->attach($student_role->uid, ['uid' => generate_uuid()]);
        }

        $this->assertTrue($user->roles()->where('code', 'STUDENT')->exists());

        // Verificar que el usuario está autenticado correctamente
        $this->assertTrue(Auth::check());
    }


    public function testHandleWithErrorMethodAuthenticatesUser()
    {
        // Limpiar usuarios de la base de datos antes de la prueba
        UsersModel::factory()->create([
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
       
        // Hacer la solicitud a la ruta del callback de LinkedIn
        $response = $this->get('/auth/callback/linkedin-openid-22');

        $response->assertStatus(500);
        $response->assertSeeText('Método de login no válido');
      
    }

    /**
     * @test
     * Prueba de redirección a la autenticación de Facebook
     */
    public function testRedirectToFacebook()
    {
        // Simular la redirección de Socialite para Facebook
        Socialite::shouldReceive('driver')->with('facebook')->andReturnSelf();
        Socialite::shouldReceive('redirect')->andReturn(redirect('https://facebook.com/login'));

        // Hacer la solicitud a la ruta de redirección a Facebook
        $response = $this->get('/auth/facebook');

        // Verificar que la respuesta redirige correctamente a la URL de Facebook
        $response->assertRedirect('https://facebook.com/login');
    }

    /**
     * @test
     * Prueba de manejo del callback de Facebook y autenticación del usuario
     */
    public function testHandleFacebookCallbackAuthenticatesUser()
    {
        // Simular la respuesta de Facebook usando Mockery
        $user_facebook_mock = Mockery::mock(\Laravel\Socialite\Contracts\User::class);
        $user_facebook_mock->shouldReceive('getEmail')->andReturn('test@example.com');
        $user_facebook_mock->email = 'test@example.com';
        $user_facebook_mock->shouldReceive('getId')->andReturn('facebook-id-123');
        $user_facebook_mock->id = 'facebook-id-123';
        $user_facebook_mock->shouldReceive('token')->andReturn('test-token');
        $user_facebook_mock->token = 'test-token';

        // Simular la autenticación de Facebook usando Socialite
        Socialite::shouldReceive('driver')->with('facebook')->andReturnSelf();
        Socialite::shouldReceive('user')->andReturn($user_facebook_mock);

        // Hacer la solicitud a la ruta del callback de Facebook
        $response = $this->get('/auth/callback/facebook');

        // Verificar que la respuesta redirige correctamente a la página principal
        $response->assertRedirect('/');
    }

}
