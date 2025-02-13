<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\CoursesModel;
use App\Models\HeaderPagesModel;
use App\Models\CourseStatusesModel;
use App\Models\GeneralOptionsModel;
use App\Models\CourseDocumentsModel;
use App\Models\CoursesPaymentsModel;
use App\Models\CoursesStudentsModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\CoursesPaymentTermsModel;
use App\Models\EducationalProgramsModel;
use App\Exceptions\OperationFailedException;
use App\Models\EducationalProgramTypesModel;
use App\Models\EducationalProgramStatusesModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EducationalProgramsPaymentTermsModel;


class CartControllerTest extends TestCase
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
     * @test Error 405
     */

    public function testReturns405ForInvalidLearningObjectType()
    {
        // Simular la variable global que espera la vista
        view()->share('existsEmailSuggestions', false);

        // Realizar la llamada a la ruta
        $response = $this->get('/cart/invalid_type/12345');

        // Verificar el estado de la respuesta
        $response->assertStatus(405);
    }


    public function testReturns405ForInvalidLearningObjectTypeCostAndGeneralOptions()
    {
        // Simular la variable global que espera la vista
        view()->share('existsEmailSuggestions', false);

        $course = CoursesModel::factory()->withCourseStatus()->withCourseType()->create(
            [
                'ects_workload' => 5,
                'payment_mode' => 'SINGLE_PAYMENT',
                'cost' => 20,
            ]
        )->first();
      

        // Realizar la llamada a la ruta
        $response = $this->get('/cart/course/' . $course->uid);

        // Verificar el estado de la respuesta
        $response->assertStatus(405);
    }
  

    /** @test*/
    public function testIndexReturnsViewForEducationalProgram()
    {
        $user = UsersModel::factory()->create()->first();
        $this->actingAs($user);

        // Simula las notificaciones generales
        $generalNotifications = [
            [
                'uid' => generate_uuid(), // Asegúrate de que esto genere un UUID válido
                'type' => 'general_notification',
                'is_read' => false,
                'title' => 'Notificación de prueba',
                'description' => 'Esta es una descripción de prueba.',
                'date' => now(), // O cualquier fecha válida
            ],
            [
                'uid' => generate_uuid(),
                'type' => 'general_notification',
                'is_read' => true,
                'title' => 'Notificación leída',
                'description' => 'Descripción de una notificación leída.',
                'date' => now()->subDays(1), // Fecha anterior para simular una notificación leída
            ],
        ];

        // Establece las variables compartidas manualmente
        View::share('general_notifications', $generalNotifications);
        View::share('unread_general_notifications', true); // Cambia a false si no hay notificaciones no leídas


        $educationalprogramtype = EducationalProgramTypesModel::factory()->create()->first();

        $options = ['redsys_enabled' => true];

        app()->instance('general_options', $options);

        // Crea un programa educativo de prueba con cursos relacionados
        $program = EducationalProgramsModel::factory()->create([
            'uid' => generate_uuid(),
            'name' => 'Test Program',
            'description' => 'This is a test program.',
            'payment_mode' => "INSTALLMENT_PAYMENT",
            'image_path' => 'images/test-images/program.png',
            'educational_program_type_uid' => $educationalprogramtype->uid
        ])->latest()->first();

        EducationalProgramsPaymentTermsModel::factory()->count(2)->create(
            [
                'educational_program_uid' => $program->uid,
            ]
        );

        // Crea cursos relacionados
        $course1 = CoursesModel::factory()->withCourseStatus()->withCourseType()->create(['ects_workload' => 5])->first();

        // Asocia los cursos al programa educativo

        $program->courses()->saveMany([$course1]);

        // Simular la variable global que espera la vista
        view()->share('existsEmailSuggestions', false);

        // Realiza la solicitud a la ruta del programa educativo
        $learning_object_type = "educational_program";
        $response = $this->get('/cart/' . $learning_object_type . '/' . $program->uid);

        // Verifica que la respuesta sea 200 y que se muestre la vista correcta
        $response->assertStatus(200);
        $response->assertViewIs('cart');
        $response->assertViewHas('learning_object_type', 'educational_program');
        $response->assertViewHas('learning_object_uid', $program->uid);
    }

    /** @test*/
    public function testIndexReturnsViewForEducationalProgramPaymentModeSingle()
    {
        $user = UsersModel::factory()->create()->first();
        $this->actingAs($user);

        // Simula las notificaciones generales
        $generalNotifications = [
            [
                'uid' => generate_uuid(), // Asegúrate de que esto genere un UUID válido
                'type' => 'general_notification',
                'is_read' => false,
                'title' => 'Notificación de prueba',
                'description' => 'Esta es una descripción de prueba.',
                'date' => now(), // O cualquier fecha válida
            ],
            [
                'uid' => generate_uuid(),
                'type' => 'general_notification',
                'is_read' => true,
                'title' => 'Notificación leída',
                'description' => 'Descripción de una notificación leída.',
                'date' => now()->subDays(1), // Fecha anterior para simular una notificación leída
            ],
        ];

        // Establece las variables compartidas manualmente
        View::share('general_notifications', $generalNotifications);
        View::share('unread_general_notifications', true); // Cambia a false si no hay notificaciones no leídas


        $educationalprogramtype = EducationalProgramTypesModel::factory()->create()->first();

        $options = ['redsys_enabled' => true];

        app()->instance('general_options', $options);

        // Crea un programa educativo de prueba con cursos relacionados
        $program = EducationalProgramsModel::factory()->create([
            'uid' => generate_uuid(),
            'name' => 'Test Program',
            'description' => 'This is a test program.',
            'payment_mode' => 'SINGLE_PAYMENT',
            'image_path' => 'images/test-images/program.png',
            'educational_program_type_uid' => $educationalprogramtype->uid
        ])->latest()->first();

        // Crea cursos relacionados
        $course1 = CoursesModel::factory()->withCourseStatus()->withCourseType()->create(['ects_workload' => 5])->first();

        // Asocia los cursos al programa educativo

        $program->courses()->saveMany([$course1]);

        // Simular la variable global que espera la vista
        view()->share('existsEmailSuggestions', false);

        // Realiza la solicitud a la ruta del programa educativo
        $learning_object_type = "educational_program";
        $response = $this->get('/cart/' . $learning_object_type . '/' . $program->uid);

        // Verifica que la respuesta sea 200 y que se muestre la vista correcta
        $response->assertStatus(200);
        $response->assertViewIs('cart');
        $response->assertViewHas('learning_object_type', 'educational_program');
        $response->assertViewHas('learning_object_uid', $program->uid);
    }

    /** @test*/
    public function testIndexReturnsViewForCourse()
    {
        $user = UsersModel::factory()->create()->first();
        $this->actingAs($user);

        // Simula las notificaciones generales
        $generalNotifications = [
            [
                'uid' => generate_uuid(), // Asegúrate de que esto genere un UUID válido
                'type' => 'general_notification',
                'is_read' => false,
                'title' => 'Notificación de prueba',
                'description' => 'Esta es una descripción de prueba.',
                'date' => now(), // O cualquier fecha válida
            ],
            [
                'uid' => generate_uuid(),
                'type' => 'general_notification',
                'is_read' => true,
                'title' => 'Notificación leída',
                'description' => 'Descripción de una notificación leída.',
                'date' => now()->subDays(1), // Fecha anterior para simular una notificación leída
            ],
        ];

        // Establece las variables compartidas manualmente
        View::share('general_notifications', $generalNotifications);
        View::share('unread_general_notifications', true); // Cambia a false si no hay notificaciones no leídas

        // Crea cursos relacionados
        $course = CoursesModel::factory()->withCourseStatus()->withCourseType()->create(
            [
                'ects_workload' => 5,
                'payment_mode' => 'INSTALLMENT_PAYMENT',
                'cost' => 0,
            ]
        )->first();

        CourseDocumentsModel::factory()->create([
            'course_uid' => $course->uid
        ]);

        CoursesPaymentTermsModel::factory()->count(2)->create(
            [
                'course_uid' => $course->uid
            ]
        );

        $options = ['redsys_enabled' => true];

        app()->instance('general_options', $options);

        // Simular la variable global que espera la vista
        view()->share('existsEmailSuggestions', false);

        // Realiza la solicitud a la ruta del programa educativo
        $learning_object_type = "course";
        $response = $this->get('/cart/' . $learning_object_type . '/' . $course->uid);

        // Verifica que la respuesta sea 200 y que se muestre la vista correcta
        $response->assertStatus(200);
        $response->assertViewIs('cart');
        $response->assertViewHas('learning_object_type', 'course');
        $response->assertViewHas('learning_object_uid', $course->uid);
    }


    /** @test Crear pago*/
    public function testMakePaymentForCourse()
    {
        // Simulamos la autenticación del usuario
        $user = UsersModel::factory()->create();
        Auth::login($user);

        $status = CourseStatusesModel::where('code', 'INSCRIPTION')->first();
        $course = CoursesModel::factory()->withCourseStatus()->withCourseType()->create([
            'course_status_uid'  => $status->uid,
            'validate_student_registrations' => false,
            'cost' => 0,
        ])->first();

        // Definimos los datos para la solicitud
        $data = [
            'learning_object_type' => "course",
            'learning_object_uid' => $course->uid,
        ];

        // Realizamos la solicitud POST
        $response = $this->postJson('/cart/make_payment', $data);

        // Verificamos que la respuesta sea correcta
        $response->assertStatus(200);

        // Verificamos que se haya creado un registro de pago
        $this->assertDatabaseHas('courses_payments', [
            'user_uid' => $user->uid,
            'course_uid' => $course->uid,
            'is_paid' => 0, // Aseguramos que aún no está pagado
        ]);

        // Verificamos que los parámetros de Redsys sean correctos en la respuesta
        $this->assertArrayHasKey('Ds_SignatureVersion', $response->json());
        $this->assertArrayHasKey('Ds_MerchantParameters', $response->json());
        $this->assertArrayHasKey('Ds_Signature', $response->json());
    }

    /** @test*/
    public function testMakePaymentInvalidCourse()
    {
        // Simulamos la autenticación del usuario
        $user = UsersModel::factory()->create();
        Auth::login($user);

        // Definimos los datos para la solicitud con un UID no válido
        $data = [
            'learning_object_type' => "course",
            'learning_object_uid' => generate_uuid(),
        ];

        // Realizamos la solicitud POST
        $response = $this->postJson('/cart/make_payment', $data);

        // Verificamos que se devuelva un error 405
        $response->assertStatus(405);
    }

    /** @test*/
    public function testMakePaymentInvalidRerquest()
    {
        // Simulamos la autenticación del usuario
        $user = UsersModel::factory()->create();
        Auth::login($user);

        // Definimos los datos para la solicitud con un UID no válido
        $data = [
            'learning_object_type' => "no-course",
            'learning_object_uid' => generate_uuid(),
        ];

        // Realizamos la solicitud POST
        $response = $this->postJson('/cart/make_payment', $data);

        // Verificamos que se devuelva un error 405
        $response->assertStatus(405);
    }

    /** @test*/
    public function testInscribeToCourseSuccessfully()
    {
        $user = UsersModel::factory()->create()->first();
        $this->actingAs($user);

        $status = CourseStatusesModel::where('code', 'INSCRIPTION')->first();
        // Crea un curso disponible para inscripción
        $course = CoursesModel::factory()->withCourseType()->create([
            'course_status_uid'  => $status->uid,
            'validate_student_registrations' => false,
            'cost' => 0,
        ])->first();

        $data = [
            'learningObjectType' => "course",
            'learningObjectUid' => $course->uid,
        ];

        // Realiza la solicitud POST a la ruta
        $response = $this->postJson('/cart/inscribe', $data);

        // Verifica que la respuesta sea exitosa
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Inscripción realizada con éxito']);
    }

    /** @test*/
    public function testInscribeToEducationalProgramSuccessfully()
    {

        $user = UsersModel::factory()->create()->first();
        $this->actingAs($user);
        $status = EducationalProgramStatusesModel::firstOrCreate(['code' => 'INSCRIPTION'], ['uid' => generate_uuid(), 'code' => 'INSCRIPTION']);
        // Crea un programa educativo disponible para inscripción
        $program = EducationalProgramsModel::factory()->withEducationalProgramType()->create([
            'educational_program_status_uid' => $status->uid,
            'validate_student_registrations' => false,
        ])->first();

        $data = [
            'learningObjectType' => 'educational_program',
            'learningObjectUid' => $program->uid,
        ];

        // Realiza la solicitud POST a la ruta
        $response = $this->postJson('/cart/inscribe', $data);

        // Verifica que la respuesta sea exitosa
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Inscripción realizada con éxito']);
    }

    /** @test*/
    public function testInscribeToEducationalProgramSuccessfullyWitCost()
    {

        $user = UsersModel::factory()->create()->first();
        $this->actingAs($user);
        $status = EducationalProgramStatusesModel::firstOrCreate(['code' => 'INSCRIPTION'], ['uid' => generate_uuid(), 'code' => 'INSCRIPTION']);
        // Crea un programa educativo disponible para inscripción
        $program = EducationalProgramsModel::factory()->withEducationalProgramType()->create([
            'educational_program_status_uid' => $status->uid,
            'validate_student_registrations' => false,
            'cost' => 100,
        ])->first();

        $data = [
            'learningObjectType' => 'educational_program',
            'learningObjectUid' => $program->uid,
        ];

        // Realiza la solicitud POST a la ruta
        $response = $this->postJson('/cart/inscribe', $data);

        // Verifica que la respuesta sea exitosa
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Inscripción realizada con éxito']);
    }


    /** @test*/
    public function testInscriptionAlreadyExists()
    {
        $user = UsersModel::factory()->create()->first();
        $this->actingAs($user);

        $status = CourseStatusesModel::where('code', 'INSCRIPTION')->first();
        // Crea un curso disponible para inscripción
        $course = CoursesModel::factory()->withCourseType()->create([
            'course_status_uid'  => $status->uid,
            'validate_student_registrations' => false,
            'cost' => 0,
        ])->first();

        CoursesStudentsModel::factory()->create([
            'user_uid' => $user->uid,
            'course_uid' => $course->uid,
        ]);

        // Datos para la solicitud
        $data = [
            'learningObjectType' => 'course',
            'learningObjectUid' => $course->uid,
        ];
        
        // Realiza la solicitud POST a la ruta
        $response= $this->postJson('/cart/inscribe', $data);
        $response->assertStatus(406);
        $response->assertJson(['message' => 'Ya estás inscrito']);
                
    }

    /** @test*/
    public function testInscriptionWithoutValidateStudentRegistrations()
    {
        $user = UsersModel::factory()->create()->first();
        $this->actingAs($user);

        $status = CourseStatusesModel::where('code', 'INSCRIPTION')->first();
        // Crea un curso disponible para inscripción
        $course = CoursesModel::factory()->withCourseType()->create([
            'course_status_uid'  => $status->uid,
            'validate_student_registrations' => false,
            'cost' => null
        ])->first();

        CoursesStudentsModel::factory()->create([
            'user_uid' => $user->uid,
            'course_uid' => $course->uid,
        ]);

        $data = [
            'learningObjectType' => 'course',
            'learningObjectUid' => $course->uid,
        ];

        $response = $this->postJson('/cart/inscribe', $data);

        $response->assertJson(['message' => "Ya estás inscrito"]);
    }


    /** @test*/
    public function testInscriptionWithoutValidateLearningObject()
    {
        $user = UsersModel::factory()->create()->first();
        $this->actingAs($user);

        $status = CourseStatusesModel::where('code', 'ENROLLING')->first();
        // Crea un curso disponible para inscripción
        $course = CoursesModel::factory()->withCourseType()->create([
            'course_status_uid'  => $status->uid,
            'validate_student_registrations' => false,
            'cost' => null
        ])->first();



        $data = [
            'learningObjectType' => 'course',
            'learningObjectUid' => $course->uid,
        ];

        $response = $this->postJson('/cart/inscribe', $data);

        $response->assertStatus(406);

        $response->assertJson(['message' => "El curso no está disponible para inscripción"]);
    }


}
