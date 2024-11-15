<?php

namespace Tests\Unit;

use App\Models\CourseDocumentsModel;
use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\CoursesModel;
use App\Models\HeaderPagesModel;
use Illuminate\Http\UploadedFile;
use App\Models\CourseStatusesModel;
use App\Models\GeneralOptionsModel;
use App\Models\CoursesStudentsModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use App\Models\CoursesStudentsDocumentsModel;
use Illuminate\Foundation\Testing\RefreshDatabase;


class InscribedCoursesControllerTest extends TestCase
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

    public function testReturnsIndexInscribedCourses()
    {

        $response = $this->get(route('my-courses-inscribed'));

        $response->assertStatus(200)
                ->assertViewIs('profile.my_courses.inscribed_courses.index')
                ->assertViewHas('resources', [
                    'resources/js/profile/my_courses/inscribed_courses.js'
                ])
                ->assertViewHas('page_title', 'Mis cursos inscritos')
                ->assertViewHas('currentPage', 'inscribedCourses');
    }

     /** @test retorna Inscripción*/
     public function testReturnsInscribedCourses()
     {

         $user = UsersModel::factory()->create();
         $this->actingAs($user);

         $status = CourseStatusesModel::where('code', 'INSCRIPTION')->first();
         // Crea un curso disponible para inscripción
         $course = CoursesModel::factory()->withCourseType()->create([
             'course_status_uid'  => $status->uid,
             'validate_student_registrations' => false,
             'cost' => 0,
         ])->first();

         CoursesStudentsModel::factory()->create([
             'uid' => generate_uuid(),
             'user_uid' => $user->uid,
             'course_uid' => $course->uid,
         ]);


        $data = [
            'items_per_page' => 10,
             'search' => 'course title'
        ];


         $response = $this->postJson('/profile/my_courses/inscribed/get', $data);


         $response->assertStatus(200)
                  ->assertJsonStructure([
                      'current_page',
                      'data' => [
                          '*' => [
                              'id',
                              'title',
                              'description',
                              // Add other fields that are expected in the response
                          ]
                      ],
                      'last_page',
                      'per_page',
                      'total',
                  ]);
     }

         /** @test */
    public function testError406UserEnrollCourseWithoutPayment()
    {
        // Arrange: Create a user and authenticate
        $user = UsersModel::factory()->create()->first();
        $this->actingAs($user);

        $status = CourseStatusesModel::where('code', 'INSCRIPTION')->first();
        // Crea un curso disponible para inscripción
        $course = CoursesModel::factory()->withCourseType()->create([
            'uid' => generate_uuid(),
            'course_status_uid'  => $status->uid,
            'cost' => 0,
        ])->first();

        CoursesStudentsModel::factory()->create([
            'user_uid' => $user->uid,
            'course_uid' => $course->uid,
        ]);

        // Act: Make a POST request to enroll in the course
        $response = $this->postJson(route('enroll-course-inscribed'), [
            'course_uid' => $course->uid,
        ]);

        // Assert: Check if the response is successful and user is enrolled
        $response->assertStatus(406)
                 ->assertJson(['message' => 'No puedes matricularte en este curso']);
    }


    /** @test curso aceptado - usuario enrolado en el curso*/
    public function testAllowsUserEnrollCourse()
    {
        // Arrange: Create a user and authenticate
        $user = UsersModel::factory()->create()->first();
        $this->actingAs($user);

        $status = CourseStatusesModel::where('code', 'ENROLLING')->first();
        // Crea un curso disponible para inscripción
        $course = CoursesModel::factory()->withCourseType()->create([
            'uid' => generate_uuid(),
            'course_status_uid'  => $status->uid,
            'cost' => 0,
        ])->first();

        $CoursesStudents = CoursesStudentsModel::factory()->create([
            'user_uid' => $user->uid,
            'course_uid' => $course->uid,
            'status' => 'INSCRIBED',
            'acceptance_status' => 'ACCEPTED'
        ])->first();

        // Act: Make a POST request to enroll in the course
        $response = $this->postJson(route('enroll-course-inscribed'), [
            'course_uid' => $course->uid,
        ]);

        // Assert: Check if the response is successful and user is enrolled
        $response->assertStatus(200)
                ->assertJson([
                    'requirePayment' => false,
                    'message' => "Matriculado en el curso correctamente"
                ]);

        // Verify that the user is now enrolled in the course
        $this->assertDatabaseHas('courses_students', [
            'user_uid' => $user->uid,
            'course_uid' => $course->uid,
            'status' => 'ENROLLED',
        ]);
    }


    /** @test Cancelar inscripción de usuario*/
    public function testAllowsUserCancelEnrollmentCourse()
    {
            // Arrange: Create a user and authenticate
        $user = UsersModel::factory()->create()->first();
        $this->actingAs($user);

        $status = CourseStatusesModel::where('code', 'ENROLLING')->first();
        // Crea un curso disponible para inscripción
        $course = CoursesModel::factory()->withCourseType()->create([
            'uid' => generate_uuid(),
            'course_status_uid'  => $status->uid,
            'cost' => 0,
        ])->first();

        $CoursesStudents = CoursesStudentsModel::factory()->create([
            'user_uid' => $user->uid,
            'course_uid' => $course->uid,
            'status' => 'INSCRIBED',
            'acceptance_status' => 'ACCEPTED'
        ])->first();

        // Act: Make a POST request to cancel enrollment
        $response = $this->postJson(route('enroll-course-cancel-inscription'), [
            'course_uid' => $course->uid,
        ]);

        // Assert: Check if the response is successful
        $response->assertStatus(200)
                    ->assertJson(['message' => 'Inscripción cancelada correctamente']);

        // Verify that the enrollment record has been deleted
        $this->assertDatabaseMissing('courses_students', [
            'course_uid' => $course->uid,
            'user_uid' => $user->uid,
            'status' => 'INSCRIBED',
        ]);
    }

     /** @test Cancelación de enrollment usuario*/
     public function testUserCancellingEnrollmentIfNotInscribed()
     {
         // Arrange: Create a user and authenticate
        $user = UsersModel::factory()->create()->first();
        $this->actingAs($user);

        $status = CourseStatusesModel::where('code', 'ENROLLING')->first();
        // Crea un curso disponible para inscripción
        $course = CoursesModel::factory()->withCourseType()->create([
            'uid' => generate_uuid(),
            'course_status_uid'  => $status->uid,
            'cost' => 0,
        ])->first();


         // Act: Make a POST request to cancel enrollment for a course not enrolled in
         $response = $this->postJson(route('enroll-course-cancel-inscription'), [
             'course_uid' => $course->uid,
         ]);

         // Assert: Check if the correct exception is thrown
         $response->assertStatus(406)
                  ->assertJson(['message' => 'No estás inscrito en este curso']);
     }


    /** @test Subir documentos un curso*/

    public function testDownloadDocumentCourse()
    {
        // Crea un usuario y actúa como él
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        $course = CoursesModel::factory()->withCourseType()->withCourseStatus()->create([
            'uid' => generate_uuid(),
            'cost' => 0,
        ])->first();

        // Crea un documento simulado en la base de datos
        $documentPath = 'documents/test_document.pdf';

        Storage::fake('local');

        Storage::put($documentPath, 'Contenido del documento');

        $courseDocument = CourseDocumentsModel::factory()->create([
            'uid' => generate_uuid(),
            'document_name' => 'test_document.pdf',
            'course_uid' => $course->uid
        ])->first();

        $documentCourse = CoursesStudentsDocumentsModel::create([
            'uid' => generate_uuid(),
            'user_uid' => $user->uid,
            'document_path' => $documentPath,
            'course_document_uid' => $courseDocument->uid,

        ]);

        // Realiza la solicitud POST para descargar el documento
        $response = $this->postJson('/profile/inscribed_courses/download_document_course', [
            'course_document_uid' => $documentCourse->uid,
        ]);

        // Verifica que la respuesta sea exitosa
        $response->assertStatus(200)
                 ->assertJsonStructure(['token']); // Verifica que se devuelva un token

        // Verifica que el token se haya guardado en la base de datos
        $this->assertDatabaseHas('backend_file_download_tokens', [
            'file' => $documentPath,
            'token' => $response->json('token'),
        ]);
    }

}
