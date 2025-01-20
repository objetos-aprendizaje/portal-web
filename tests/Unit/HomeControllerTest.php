<?php

namespace Tests\Unit;


use Mockery;
use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\BlocksModel;
use App\Models\CoursesModel;
use App\Models\UserLanesModel;
use Illuminate\Support\Carbon;
use App\Models\CategoriesModel;
use Illuminate\Support\Facades\DB;
use App\Models\CourseStatusesModel;
use App\Models\GeneralOptionsModel;
use App\Services\EmbeddingsService;
use App\Models\LearningResultsModel;
use Illuminate\Support\Facades\View;
use App\Models\CoursesEmbeddingsModel;
use Illuminate\Support\Facades\Config;
use App\Models\CoursesAssessmentsModel;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\HomeController;
use App\Models\EducationalProgramsModel;
use App\Models\EducationalResourcesModel;
use App\Models\SlidersPrevisualizationsModel;
use App\Models\EducationalProgramStatusesModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EducationalProgramsAssessmentsModel;
use App\Models\EducationalResourcesEmbeddingsModel;

class HomeControllerTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Configura el módulo de recomendaciones como habilitado
        Config::set('general_options.enabled_recommendation_module', true);
    }

    public function testIndexReturnsCorrectViewWithData()
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

        $user2 = UsersModel::factory()->create();

        // Simulamos los datos que debería devolver el modelo GeneralOptions
        GeneralOptionsModel::factory()->create([
            'option_name' => 'site_name',
            'option_value' => 'Mi Sitio'
        ]);

        GeneralOptionsModel::factory()->create([
            'option_name' => 'site_description',
            'option_value' => 'Descripción del sitio'
        ]);

        $status = CourseStatusesModel::where('code', 'INSCRIPTION')->first();

        // Simulamos los datos que debería devolver el modelo Courses
        $course1 = CoursesModel::factory()->withCourseStatus()->withCourseType()->create([
            'featured_big_carrousel' => true,
            'featured_big_carrousel_approved' => true,
            'course_status_uid' => $status->uid,
        ]);

        CoursesAssessmentsModel::factory()->create(
            [
                'course_uid' => $course1->uid,
                'user_uid' => $user->uid,
            ]
        );

        $course3 = CoursesModel::factory()->withCourseType()->create([
            'featured_small_carrousel' => true,
            'featured_small_carrousel_approved' => true,
            'course_status_uid' => $status->uid
        ]);

        CoursesAssessmentsModel::factory()->create(
            [
                'course_uid' => $course3->uid,
                'user_uid' => $user->uid,
            ]
        );


        $educationalStatus = EducationalProgramStatusesModel::where('code', 'ACCEPTED_PUBLICATION')->first();

        // Simulamos los datos que debería devolver el modelo EducationalPrograms
        $educational1 = EducationalProgramsModel::factory()->withEducationalProgramType()->create([
            'featured_slider' => true,
            'featured_slider_approved' => true,
            'educational_program_status_uid' => $educationalStatus->uid,
        ]);

        EducationalProgramsAssessmentsModel::factory()->create(
            [
                'user_uid' => $user->uid,
                'educational_program_uid' => $educational1->uid,
            ]
        );

        $educational2 = EducationalProgramsModel::factory()->withEducationalProgramType()->create([
            'featured_main_carrousel' => true,
            'featured_main_carrousel_approved' => true,
            'educational_program_status_uid' => $educationalStatus->uid,
        ]);

        EducationalProgramsAssessmentsModel::factory()->create(
            [
                'user_uid' => $user->uid,
                'educational_program_uid' => $educational2->uid,
            ]
        );

        $slider = SlidersPrevisualizationsModel::factory()->create();


        // Hacer una solicitud HTTP a la ruta del método index

        $response = $this->get(route('index', ['previsualize-slider' => $slider->uid]));

        // Comprobamos que el estado de la respuesta es 200
        $response->assertStatus(200);

        // Comprobamos que la vista cargada es la correcta
        $response->assertViewIs('home');

        // Verificar que la vista contiene los datos necesarios
        $response->assertViewHas('resources');
        $response->assertViewHas('featured_courses');
        $response->assertViewHas('general_options');
    }

    public function testIndexReturnsCorrectViewWithDataNotLogged()
    {
        // Buscamos un usuario  
        $user = UsersModel::where('email', 'admin@admin.com')->first();

        // Si no existe el usuario lo creamos
        if (!$user) {
            $user = UsersModel::factory()->create([
                'email' => 'admin@admin.com'
            ])->first();
        }

        // Simulamos los datos que debería devolver el modelo GeneralOptions
        GeneralOptionsModel::factory()->create([
            'option_name' => 'site_name',
            'option_value' => 'Mi Sitio'
        ]);

        GeneralOptionsModel::factory()->create([
            'option_name' => 'site_description',
            'option_value' => 'Descripción del sitio'
        ]);

        $status = CourseStatusesModel::where('code', 'INSCRIPTION')->first();

        // Simulamos los datos que debería devolver el modelo Courses
        $course1 = CoursesModel::factory()->withCourseStatus()->withCourseType()->create([
            'featured_big_carrousel' => true,
            'featured_big_carrousel_approved' => true,
            'course_status_uid' => $status->uid,
        ]);

        $course3 = CoursesModel::factory()->withCourseType()->create([
            'featured_small_carrousel' => true,
            'featured_small_carrousel_approved' => true,
            'course_status_uid' => $status->uid
        ]);

        $educationalStatus = EducationalProgramStatusesModel::where('code', 'ACCEPTED_PUBLICATION')->first();

        // Simulamos los datos que debería devolver el modelo EducationalPrograms
        $educational1 = EducationalProgramsModel::factory()->withEducationalProgramType()->create([
            'featured_slider' => true,
            'featured_slider_approved' => true,
            'educational_program_status_uid' => $educationalStatus->uid,
        ]);

        EducationalProgramsAssessmentsModel::factory()->create(
            [
                'user_uid' => $user->uid,
                'educational_program_uid' => $educational1->uid,
            ]
        );

        $educational2 = EducationalProgramsModel::factory()->withEducationalProgramType()->create([
            'featured_main_carrousel' => true,
            'featured_main_carrousel_approved' => true,
            'educational_program_status_uid' => $educationalStatus->uid,
        ]);

        EducationalProgramsAssessmentsModel::factory()->create(
            [
                'user_uid' => $user->uid,
                'educational_program_uid' => $educational2->uid,
            ]
        );
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

    /**
     * @test
     * Prueba que el método getMyEducationalResources devuelve los recursos educativos correctos
     */
    public function testGetMyEducationalResourcesReturnsCorrectData()
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

        // Crear recursos educativos
        $resource1 = EducationalResourcesModel::factory()
            ->withStatus()
            ->withEducationalResourceType()
            ->withCreatorUser()
            ->create();

        $resource2 = EducationalResourcesModel::factory()
            ->withStatus()
            ->withEducationalResourceType()
            ->withCreatorUser()->create();

        $resource3 = EducationalResourcesModel::factory()
            ->withStatus()
            ->withEducationalResourceType()
            ->withCreatorUser()->create();

        // Asociar los recursos educativos al usuario
        DB::table('educational_resource_access')->insert([
            ['uid' => generate_uuid(), 'date' => Carbon::now(), 'educational_resource_uid' => $resource1->uid, 'user_uid' => $user->uid],
            ['uid' => generate_uuid(), 'date' => Carbon::now(), 'educational_resource_uid' => $resource2->uid, 'user_uid' => $user->uid],
        ]);

        // Preparar datos de la solicitud
        $requestData = [
            'items_per_page' => 10,
        ];

        // Hacer la solicitud POST a la ruta de obtener recursos educativos
        $response = $this->post(route('get-my-educational-resources'), $requestData);

        // Verificar que la respuesta sea exitosa
        $response->assertStatus(200);

        // Verificar que los recursos educativos asociados al usuario se devuelven correctamente
        $response->assertJsonFragment(['uid' => $resource1->uid]);
        $response->assertJsonFragment(['uid' => $resource2->uid]);

        // Verificar que los recursos educativos no asociados al usuario no se devuelven
        $response->assertJsonMissing(['uid' => $resource3->uid]);
    }

    /**
     * @test
     * Prueba para obtener los cursos recomendados según las preferencias del usuario
     */
    public function testGetRecommendedCoursesReturnsCorrectData()
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


        $general = GeneralOptionsModel::where('option_name', 'enabled_recommendation_module')->first();
        $general->option_value = true;
        $general->save();

        // Crear categorías simuladas y asignarlas al usuario
        CategoriesModel::factory()->count(3)->create();

        $categories = CategoriesModel::get();

        foreach ($categories as $category) {
            $user->categories()->attach(
                $category->uid,
                [
                    'uid' => generate_uuid(),
                ]
            );
        }

        // Crear resultados de aprendizaje simulados y asignarlos al usuario
        LearningResultsModel::factory()
            ->withCompetence()->count(3)->create();

        $learningResults = LearningResultsModel::get();

        foreach ($learningResults as $learningResult) {

            $user->learningResultsPreferences()->attach(
                $learningResult->uid
            );
        }

        // Crear cursos simulados en los que el usuario está inscrito
        $courses1 = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->count(2)->create();


        foreach ($courses1 as $course) {

            CoursesEmbeddingsModel::factory()->create(
                [
                    'course_uid' => $course->uid,
                ]
            );
        }


        $courses = CoursesModel::get();

        foreach ($courses as $course) {
            $user->courses_students()->attach($course->uid, ['uid' => generate_uuid()]);
        }

        // Mock del servicio de embeddings para devolver un curso recomendado
        $similarCourse = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create();

        // $this->mock(EmbeddingsService::class, function ($mock) use ($similarCourse) {
        //     $mock->shouldReceive('getSimilarCoursesList')
        //         ->andReturn(collect([$similarCourse]));
        // });

        // Crear datos de la solicitud
        $requestData = [
            'items_per_page' => 5,
            'page' => 1,
        ];

        // Realizar la solicitud POST a la ruta
        $response = $this->post(route('get-recommended-courses'), $requestData);

        // Verificar que la respuesta sea exitosa
        $response->assertStatus(200);

        // Verificar que los datos del curso recomendado están presentes en la respuesta
        // $response->assertJsonFragment([
        //     'uid' => $similarCourse->uid,
        // ]);
    }

    /**
     * @test
     * Prueba para obtener los cursos recomendados con error
     */
    public function testGetRecommendedCoursesReturnsFailGeneralOption()
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

        // Crear datos de la solicitud
        $requestData = [
            'items_per_page' => 5,
            'page' => 1,
        ];

        $response = $this->post(route('get-recommended-courses'), $requestData);

        // Verificar que la respuesta sea no exitosa
        $response->assertStatus(406);
        $response->assertJson(['message' => '']);
    }

    /**
     * @test
     * Prueba para obtener los cursos recomendados con error sin CoursesUser
     */
    public function testGetRecommendedCoursesReturnsFailCoursesUser()
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

        // Crear datos de la solicitud
        $requestData = [
            'items_per_page' => 5,
            'page' => 1,
        ];

        $general = GeneralOptionsModel::where('option_name', 'enabled_recommendation_module')->first();
        $general->option_value = true;
        $general->save();

        $response = $this->post(route('get-recommended-courses'), $requestData);

        // Verificar que la respuesta sea no exitosa
        $response->assertStatus(406);
        $response->assertJson(['message' => 'No courses found for the user']);
    }

    /**
     * @test
     * Prueba que el método getRecommendedEducationalResources devuelve los recursos educativos recomendados correctamente
     */
    public function testGetRecommendedEducationalResourcesReturnsCorrectRecommendations()
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

        $general = GeneralOptionsModel::where('option_name', 'enabled_recommendation_module')->first();
        $general->option_value = true;
        $general->save();

        $options = ['enabled_recommendation_module' => true];

        app()->instance('general_options', $options);


        // Crear categorías y resultados de aprendizaje asociados al usuario
        $category = CategoriesModel::factory()->create();

        $learningResult = LearningResultsModel::factory()->withCompetence()->create();

        $user->categories()->attach($category->uid, ['uid' => generate_uuid()]);
        $user->learningResultsPreferences()->attach($learningResult->uid);

        // Crear un vector de 1536 dimensiones para los embeddings
        $embeddingVector = '[' . implode(',', array_fill(0, 1536, '0.1')) . ']';

        // Crear recursos educativos con los que el usuario ha interactuado
        $resource1 = EducationalResourcesModel::factory()
            ->withEducationalResourceType()
            ->withStatus()
            ->create(
                [
                    'creator_user_uid' => $user->uid,
                ]
            );

        EducationalResourcesEmbeddingsModel::factory()->create(
            [
                'educational_resource_uid' => $resource1->uid,
            ]
        );

        $resource2 = EducationalResourcesModel::factory()
            ->withEducationalResourceType()
            ->withStatus()->create([
                'creator_user_uid' => $user->uid,
            ]);

        EducationalResourcesEmbeddingsModel::factory()->create(
            [
                'educational_resource_uid' =>  $resource2->uid,
            ]
        );

        $user->educationalResources()->attach(
            $resource1->uid,
            [
                'uid' => generate_uuid(),
                'date' => Carbon::now(),
            ]
        );
        $user->educationalResources()->attach(
            $resource2->uid,
            [
                'uid' => generate_uuid(),
                'date' => Carbon::now(),
            ]
        );

        // Simular el resultado del método del servicio
        $similarResource = EducationalResourcesModel::factory()
            ->withCreatorUser()
            ->withEducationalResourceType()
            ->withStatus()->create();

        EducationalResourcesEmbeddingsModel::factory()->create(
            [
                'educational_resource_uid' => $similarResource->uid,
            ]
        );

        $similarResources = new \Illuminate\Pagination\LengthAwarePaginator(
            collect([$similarResource]), // Los datos
            1, // Total de elementos
            5, // Elementos por página
            1  // Página actual
        );

        // Mock del servicio embeddingsService
        $this->mock(EmbeddingsService::class, function ($mock) use ($similarResources) {

            $mock->shouldReceive('getSimilarEducationalResourcesList')
                ->andReturn(collect([$similarResources]));
        });

        // Preparar datos de la solicitud
        $requestData = [
            'items_per_page' => 5,
            'page' => 1,
        ];


        // Hacer la solicitud POST a la ruta de obtener recursos educativos recomendados
        $response = $this->post(route('get-recommended-educational-resources'), $requestData);

        // Verificar que la respuesta sea exitosa
        $response->assertStatus(200);
        $response->assertViewHas('general_options');

        // Verificar que los recursos educativos recomendados se devuelven correctamente
        // $response->assertJsonFragment(['uid' => $similarResource->uid]);
    }


    /**
     * @test
     * Prueba que el método getRecommendedEducationalResources con error
     */
    public function testGetRecommendedEducationalResourcesReturnsFailGeneralOptions()
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


        // Hacer la solicitud POST a la ruta de obtener recursos educativos recomendados
        $response = $this->post(route('get-recommended-educational-resources'), []);

        // Verificar que la respuesta sea exitosa
        $response->assertStatus(406);
    }


    /**
     * @test
     * Prueba que el método getRecommendedEducationalResources con error
     */
    public function testGetRecommendedEducationalResourcesReturnsFailEducationalResourcesUser()
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

        $general = GeneralOptionsModel::where('option_name', 'enabled_recommendation_module')->first();
        $general->option_value = true;
        $general->save();

        // Preparar datos de la solicitud
        $requestData = [
            'items_per_page' => 5,
            'page' => 1,
        ];

        // Hacer la solicitud POST a la ruta de obtener recursos educativos recomendados
        $response = $this->post(route('get-recommended-educational-resources'), $requestData);

        // Verificar que la respuesta sea exitosa
        $response->assertStatus(406);

        $response->assertJson(['message' => 'No educational resources found for the user']);
    }


    /**
     * @test
     * Prueba que el itinerario recomendado se devuelve correctamente.
     */
    public function testGetRecommendedItineraryReturnsCorrectItinerary()
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

        $user2 = UsersModel::factory()->create();

        // Simular resultados de aprendizaje del usuario
        LearningResultsModel::factory()->count(2)->withCompetence()->create();
        $learningResults = LearningResultsModel::get();

        foreach ($learningResults as $learningResult) {
            // Asignar los resultados de aprendizaje al usuario
            $user->learningResultsPreferences()->attach($learningResult->uid);
        }

        // Crear los cursos que cubre uno de los resultados de aprendizaje

        CoursesModel::factory()
            ->withCourseStatus()->withCourseType()->count(2)
            ->create();

        $courses = CoursesModel::get();

        foreach ($courses as $course) {

            $course->students()->attach($user->uid, ['uid' => generate_uuid()]);
        }

        $block1 = BlocksModel::factory()->create(['course_uid' => $courses[0]->uid])->first();

        $block1->learningResults()->attach(
            $learningResults[0]->uid,
            [
                'uid' => generate_uuid(),
            ]
        );

        $block2 = BlocksModel::factory()->create(['course_uid' => $courses[1]->uid]);

        $block2->learningResults()->attach($learningResults[1]->uid, [
            'uid' => generate_uuid(),
        ]);

        $learning = LearningResultsModel::factory()->withCompetence()->create();

        // Simular que el usuario está inscrito en un curso
        $course3 = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()->create();

        $course3->students()->attach($user2->uid, ['uid' => generate_uuid()]);

        $user2->learningResultsPreferences()->attach($learning->uid);

        // Crear la solicitud con el número de elementos por página
        $requestData = ['items_per_page' => 2];

        // Hacer la solicitud POST a la ruta de obtener itinerario recomendado
        $response = $this->postJson(route('get-recommended-itinerary'), $requestData);

        // Verificar que la respuesta sea exitosa
        $response->assertStatus(200);

        // // Verificar que el itinerario recomendado se devuelva correctamente
        // $response->assertJsonFragment(['uid' => $courses[0]->uid]);
        // $response->assertJsonFragment(['uid' => $courses[1]->uid]);

        // Verificar que el curso en el que el usuario está inscrito no se incluya en la respuesta
        $response->assertJsonMissing(['uid' => $course3->uid]);
    }
}
