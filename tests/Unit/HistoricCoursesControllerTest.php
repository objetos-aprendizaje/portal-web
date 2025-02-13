<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\CoursesModel;
use App\Models\HeaderPagesModel;
use App\Models\CourseStatusesModel;
use App\Models\GeneralOptionsModel;
use Illuminate\Support\Facades\View;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Profile\MyCourses\HistoricCoursesController;


class HistoricCoursesControllerTest extends TestCase
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
     * @test Index histórico cursos
     */

    public function testIndexHistoricCourses()
    {
        $response = $this->get(route('my-courses-historic'));

        $response->assertStatus(200);
        $response->assertViewIs('profile.my_courses.historic_courses.index');
        $response->assertViewHas('resources');
        $response->assertViewHas('page_title');
        $response->assertViewHas('currentPage');
    }

    /**
     * @test Vista Historico de cursos
     */

    public function testHistoricCoursesMethod()
    {
        $controller = new HistoricCoursesController();
        $view = $controller->index();

        $this->assertInstanceOf('Illuminate\View\View', $view);
        $this->assertEquals('profile.my_courses.historic_courses.index', $view->getName());
        $this->assertArrayHasKey('resources', $view->getData());
        $this->assertArrayHasKey('page_title', $view->getData());
        $this->assertArrayHasKey('currentPage', $view->getData());
    }

    /**
     * @test Muestra lista de cursos
     */

    public function testGetHistoricCourses()
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

        $status = CourseStatusesModel::where('code', 'FINISHED')->first();

        $course = CoursesModel::factory()
            ->withCourseType()
            ->create(
                [
                    'course_status_uid' => $status->uid,
                ]
            );

        $user->courses_students()->attach($course->uid, [
            'uid' => generate_uuid(),
            'status' => 'ENROLLED',
        ]);

        $response = $this->post(route('get-historic-courses'), [
            'items_per_page' => 5,
            'search' => $course->title,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => [
                    'uid',
                    'title',
                ]
            ],
            'from',
            'last_page',
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total'
        ]);
    }

    /**
     * @test Acceso a cursos
     *
     */
    public function testAccessCourse()
    {
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        $status = CourseStatusesModel::firstOrCreate(['code' => 'FINISHED'], ['uid' => generate_uuid(), 'code' => 'FINISHED']);
        // Crea un curso disponible para inscripción
        $course = CoursesModel::factory()->withCourseType()->create([
            'course_status_uid'  => $status->uid,
            'validate_student_registrations' => false,
            'cost' => 0,
            'lms_url' => 'http://example.com/course',
        ])->first();
        $course->status()->associate($status);
        $course->save();

        // Realizar la solicitud POST
        $response = $this->post(route('historic-courses-access'), [
            'courseUid' => $course->uid
        ]);

        // Verificar la respuesta
        $response->assertStatus(200);
        $response->assertJson([
            'lmsUrl' => 'http://example.com/course'
        ]);

        // Verificar que se haya creado un registro de acceso
        $this->assertDatabaseHas('courses_accesses', [
            'course_uid' => $course->uid,
            'user_uid' => $user->uid,
        ]);
    }

    public function testAccessCourseForbiddenWhenNotFinished()
    {
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        $status = CourseStatusesModel::firstOrCreate(['code' => 'DEVELOPMENT'], ['uid' => generate_uuid(), 'code' => 'DEVELOPMENT', 'name' => 'En desarrollo']);
        // Crea un curso disponible para inscripción
        $course = CoursesModel::factory()->withCourseType()->create([
            'course_status_uid'  => $status->uid,
            'validate_student_registrations' => false,
            'cost' => 0,
            'lms_url' => 'http://example.com/course-not-finished',
        ])->first();
        $course->status()->associate($status);
        $course->save();

        // Realizar la solicitud POST
        $response = $this->post(route('historic-courses-access'), [
            'courseUid' => $course->uid
        ]);

        // Verificar que se reciba un error 403
        $response->assertStatus(403);
    }
}
