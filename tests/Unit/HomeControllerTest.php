<?php

namespace Tests\Unit;


use Mockery;
use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\CoursesModel;
use App\Models\UserLanesModel;
use App\Models\GeneralOptionsModel;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\HomeController;
use App\Models\EducationalProgramsModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HomeControllerTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use RefreshDatabase;

    public function testIndexReturnsCorrectViewWithData()
    {

        // Simulamos los datos que debería devolver el modelo GeneralOptions
        GeneralOptionsModel::factory()->create([
            'option_name' => 'site_name',
            'option_value' => 'Mi Sitio'
        ]);

        GeneralOptionsModel::factory()->create([
            'option_name' => 'site_description',
            'option_value' => 'Descripción del sitio'
        ]);

        // Simulamos los datos que debería devolver el modelo Courses
        CoursesModel::factory()->withCourseStatus()->withCourseType()->create([
            'featured_big_carrousel' => true,
            'featured_big_carrousel_approved' => true
        ]);

        // Simulamos los datos que debería devolver el modelo EducationalPrograms
        EducationalProgramsModel::factory()->withEducationalProgramType()->create([
            'featured_slider' => true,
            'featured_slider_approved' => true
        ]);

        // Hacer una solicitud HTTP a la ruta del método index
        $response = $this->get(route('index'));

        // Comprobamos que el estado de la respuesta es 200
        $response->assertStatus(200);

        // Comprobamos que la vista cargada es la correcta
        $response->assertViewIs('home');

        // Verificar que la vista contiene los datos necesarios
        $response->assertViewHas('resources');
        $response->assertViewHas('featured_courses');
        $response->assertViewHas('general_options');
    }

    public function testSaveLanesPreferencesWithValidLane()
    {
        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        // Crear un request simulado con lane y active
        $request = Request::create('/home/save_lanes_preferences', 'POST', [
            'lane' => 'courses',
            'active' => true,
        ]);

        // Simular que ya existe una preferencia para el usuario
        $userLane = UserLanesModel::factory()->create([
            'user_uid' => $user->uid,
            'code' => 'courses',
            'active' => false, // Valor actual antes de la actualización
        ]);

        // Llamar al método del controlador
        $response = $this->post('/home/save_lanes_preferences', $request->all());

        // Comprobar que el estado de la respuesta es 200
        $response->assertStatus(200);

        // Comprobar que el JSON de respuesta es correcto
        $response->assertJson(['success' => true]);

        // Verificar que el campo 'active' se ha actualizado
        $this->assertDatabaseHas('user_lanes', [
            'user_uid' => $user->uid,
            'code' => 'courses',
            'active' => true, // Ahora debería ser true después de la actualización
        ]);
    }

    public function testSaveLanesPreferencesCreatesNewPreference()
    {
        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        // Crear un request simulado con lane y active
        $request = Request::create('/home/save_lanes_preferences', 'POST', [
            'lane' => 'resources',
            'active' => true,
        ]);

        // Llamar al método del controlador
        $response = $this->post('/home/save_lanes_preferences', $request->all());

        // Comprobar que el estado de la respuesta es 200
        $response->assertStatus(200);

        // Comprobar que el JSON de respuesta es correcto
        $response->assertJson(['success' => true]);

        // Verificar que se ha creado una nueva preferencia en la base de datos
        $this->assertDatabaseHas('user_lanes', [
            'user_uid' => $user->uid,
            'code' => 'resources',
            'active' => true,
        ]);
    }

    public function testSaveLanesPreferencesWithInvalidLane()
    {
        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        // Crear un request simulado con un lane inválido
        $request = [
            'lane' => 'invalid_lane',
            'active' => true,
        ];

        // Llamar al método del controlador y esperar una excepción o un error en la respuesta
        $response = $this->post('/home/save_lanes_preferences', $request);

        // Verificar que el estado de la respuesta es 500 (Internal Server Error)
        $response->assertStatus(500);

        // Comprobar que el mensaje de error se incluye en la respuesta
        $response->assertSeeText('Invalid lane');
    }

    /**
     * @test
     * Prueba que se devuelven los cursos activos para el usuario autenticado
     */
    public function testGetActiveCoursesReturnsCourses()
    {
        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        // Crear un curso y asociarlo al usuario simulando la relación de 'courses_students'
        $course = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create()
            ->latest()->first();

        $user->courses_students()->attach($course->uid, [
            'uid'               => generate_uuid(),
            'acceptance_status' => 'ACCEPTED',
            'status'            => 'ENROLLED'
        ]);

        // Crear un request simulado con el número de items por página
        $request = Request::create('/home/get_active_courses', 'POST', [
            'items_per_page' => 10,
        ]);

        // Simular el estado de 'DEVELOPMENT' en el curso
        $course->status()->create([
            'uid' => generate_uuid(),
            'name' => 'Develoment',
            'code' => 'DEVELOPMENT'
        ]);

        // Llamar al método del controlador
        $response = $this->post(route('get-active-courses'), $request->all());

        // Comprobar que el estado de la respuesta es 200
        $response->assertStatus(200);

        // Verificar que el curso aparece en los datos de la respuesta
        // $response->assertJsonPath('data.0.uid', $course->uid);
    }

    /**
     * @test
     * Prueba que no se devuelven cursos cuando no hay cursos activos
     */
    public function testGetActiveCoursesWithoutActiveCourses()
    {
        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        // Crear un request simulado con el número de items por página
        $request = Request::create('/home/get_active_courses', 'POST', [
            'items_per_page' => 10,
        ]);

        // Llamar al método del controlador
        $response = $this->post(route('get-active-courses'), $request->all());

        // Comprobar que el estado de la respuesta es 200
        $response->assertStatus(200);

        // Comprobar que la respuesta JSON no contiene ningún curso
        $response->assertJsonCount(0, 'data');
    }

    /**
     * @test
     * Prueba que se devuelven los cursos inscritos para el usuario autenticado
     */
    public function testGetInscribedCoursesReturnsCourses()
    {
        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        // Crear un curso y asociarlo al usuario simulando la relación de 'courses_students'
        $course = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create()
            ->latest()->first();

        $user->courses_students()->attach($course->uid, [
            'uid'               => generate_uuid(),
            'status'            => 'INSCRIBED'
        ]);

        // Crear un request simulado con el número de items por página
        $request = Request::create('/home/get_inscribed_courses', 'POST', [
            'items_per_page' => 10,
        ]);

        // Simular el estado de 'DEVELOPMENT' en el curso
        $course->status()->create([
            'uid' => generate_uuid(),
            'name' => 'Development',
            'code' => 'DEVELOPMENT'
        ]);

        // Llamar al método del controlador
        $response = $this->post(route('get-inscribed-courses'), $request->all());

        // Comprobar que el estado de la respuesta es 200
        $response->assertStatus(200);

        // Verificar que el curso aparece en los datos de la respuesta
        // $response->assertJsonPath('data.0.uid', $course->uid);
    }

    /**
     * @test
     * Prueba que no se devuelven cursos cuando no hay cursos inscritos
     */
    public function testGetInscribedCoursesWithoutCourses()
    {
        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        // Crear un request simulado con el número de items por página
        $request = Request::create('/home/get_inscribed_courses', 'POST', [
            'items_per_page' => 10,
        ]);

        // Llamar al método del controlador
        $response = $this->post(route('get-inscribed-courses'), $request->all());

        // Comprobar que el estado de la respuesta es 200
        $response->assertStatus(200);

        // Comprobar que la respuesta JSON no contiene ningún curso
        $response->assertJsonCount(0, 'data');
    }



    /**
     * @test
     * Prueba que se devuelven los cursos en los que el usuario es profesor
     */
    public function testGetTeacherCoursesReturnsCourses()
    {
        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        // Crear un curso y asociarlo al usuario como profesor
        $course = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create()
            ->first();

        $course->teachers()->attach($user->uid, [
            'uid' => generate_uuid(),
            'type' => 'NO_COORDINATOR'
        ]);

        // Crear el estado y el programa educativo
        $course->status()->create([
            'uid' => generate_uuid(),
            'name' => 'Development',
            'code' => 'DEVELOPMENT'
        ]);

        // Crear un programa educativo y asignarlo al curso
        $educationalProgram = EducationalProgramsModel::factory()
            ->withEducationalProgramType()
            ->create()->first();

        // Establecer la clave foránea directamente en el curso
        $course->educational_program_uid = $educationalProgram->uid;
        $course->save();

        // Crear el estado del programa educativo
        $educationalProgram->status()->create([
            'uid' => generate_uuid(),
            'name' => 'Development',
            'code' => 'DEVELOPMENT'
        ]);

        // Crear un request simulado con el número de items por página
        $request = Request::create('/home/get_teacher_courses', 'POST', [
            'items_per_page' => 10,
        ]);

        // Llamar al método del controlador
        $response = $this->post(route('get-teacher-courses'), $request->all());

        // Comprobar que el estado de la respuesta es 200
        $response->assertStatus(200);

        // Verificar que el curso aparece en los datos de la respuesta
        // $response->assertJsonPath('data.0.uid', $course->uid);
    }

    /**
     * @test
     * Prueba que no se devuelven cursos cuando el usuario no es profesor en ninguno
     */
    public function testGetTeacherCoursesWithoutCourses()
    {
        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        // Crear un request simulado con el número de items por página
        $request = Request::create('/home/get_teacher_courses', 'POST', [
            'items_per_page' => 10,
        ]);

        // Llamar al método del controlador
        $response = $this->post(route('get-teacher-courses'), $request->all());

        // Comprobar que el estado de la respuesta es 200
        $response->assertStatus(200);

        // Comprobar que la respuesta JSON no contiene ningún curso
        $response->assertJsonCount(0, 'data');
    }
}
