<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\CoursesModel;
use App\Models\HeaderPagesModel;
use App\Models\GeneralOptionsModel;
use App\Models\CoursesAccessesModel;
use Illuminate\Support\Facades\View;
use App\Models\EducationalProgramsModel;
use App\Exceptions\OperationFailedException;
use App\Models\EducationalProgramStatusesModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EducationalProgramsPaymentTermsModel;
use App\Models\EducationalProgramsPaymentTermsUsersModel;

class EnrolledEducationalProgramsControllerTest extends TestCase
{
    use RefreshDatabase;

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
     * @test
     * Prueba que la vista de programas formativos matriculados se carga correctamente con los datos necesarios
     */
    public function testIndexLoadsEnrolledEducationalProgramsPageWithCorrectData()
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

        $general_options  = [
            'redsys_url' => 'http://midominio.com',
            'color_1' => '#333333',
            'color_2' => '#222',
            'color_3' => '#111',
            'color_4' => '#000',
            'scripts' => '<script src="jquery.js"></script>',
            'poa_logo_1' => 'image1.png',
            'poa_logo_2' => 'image2.png',
            'poa_logo_3' => 'image3.png',
            'registration_active' => false,
        ];

        app()->instance('general_options', $general_options);

        View::share('general_options', $general_options);

        View::share('unread_general_notifications', true);

        // Hacer una solicitud GET a la ruta de programas formativos matriculados
        $response = $this->get(route('my-educational-programs-enrolled'));

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que la vista correcta se cargue
        $response->assertViewIs('profile.my_educational_programs.enrolled_educational_programs.index');

        // Verificar que los recursos JavaScript y el título de la página se pasan correctamente
        $response->assertViewHas('resources', ['resources/js/profile/my_educational_programs/enrolled_educational_programs.js']);
        $response->assertViewHas('page_title', 'Mis programas formativos matriculados');
        $response->assertViewHas('currentPage', 'enrolledEducationalPrograms');
    }



    /**
     * @test
     * Prueba que los programas formativos matriculados se devuelven correctamente con paginación
     */
    public function testGetEnrolledEducationalProgramsReturnsCorrectData()
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

        $status = EducationalProgramStatusesModel::where('code', 'DEVELOPMENT')->first();

        // Crear algunos programas formativos y matricular al usuario
        $educationalPrograms = EducationalProgramsModel::factory()
            ->withEducationalProgramType()
            ->count(3)->create(
                [
                    'educational_program_status_uid' => $status->uid
                ]
            );
        foreach ($educationalPrograms as $educationalProgram) {

            $user->educationalPrograms()->attach($educationalProgram->uid, [
                'uid' => generate_uuid(),
                'status' => 'ENROLLED',
                'acceptance_status' => 'ACCEPTED'
            ]);

            CoursesModel::factory()
                ->withCourseStatus()
                ->withCourseType()
                ->create([
                    'educational_program_uid' => $educationalProgram->uid,
                ]);

            // Crear un término de pago
            $paymentTerm = EducationalProgramsPaymentTermsModel::factory()->create([
                'educational_program_uid' => $educationalProgram->uid,
            ]);

            // Crear un único registro de usuario asociado al término de pago
            EducationalProgramsPaymentTermsUsersModel::factory()->create([
                'educational_program_payment_term_uid' => $paymentTerm->uid,
                'user_uid' => $user->uid,
                'is_paid' => true,
            ]);

            // Simular que la relación userPayment sea una colección
            $paymentTerm->setRelation('userPayment', collect([
                EducationalProgramsPaymentTermsUsersModel::where('educational_program_payment_term_uid', $paymentTerm->uid)->first(),
            ]));
        }

        // Crear una solicitud simulada con paginación
        $requestData = [
            'items_per_page' => 2,
            'search' => null
        ];

        // Hacer la solicitud POST a la ruta de obtener programas formativos matriculados
        $response = $this->post(route('my-educational-programs-enrolled-get'), $requestData);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que los programas formativos matriculados se devuelven correctamente
        $response->assertJsonFragment([
            'uid' => $educationalPrograms[0]->uid,
        ]);
    }

    /**
     * @test
     * Prueba que la búsqueda de programas formativos matriculados funciona correctamente
     */
    public function testGetEnrolledEducationalProgramsSearchWorks()
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



        $user1 = UsersModel::factory()->create();

        $status = EducationalProgramStatusesModel::where('code', 'DEVELOPMENT')->first();


        $educationalProgram = EducationalProgramsModel::factory()
            ->withEducationalProgramType()
            ->create(
                [
                    'educational_program_status_uid' => $status->uid
                ]
            );

        CoursesModel::factory()->withCourseStatus()->withCourseType()->create(
            [
                'educational_program_uid' =>  $educationalProgram->uid
            ]
        );

        $user->educationalPrograms()->attach($educationalProgram->uid, [
            'uid' => generate_uuid(),
            'status' => 'ENROLLED',
            'acceptance_status' => 'ACCEPTED'
        ]);


        $paymentTerm = EducationalProgramsPaymentTermsModel::factory()->create(
            [
                'educational_program_uid' => $educationalProgram->uid
            ]
        );

        $paymentTerm1 = EducationalProgramsPaymentTermsModel::factory()->create(
            [
                'educational_program_uid' => $educationalProgram->uid
            ]
        );

        EducationalProgramsPaymentTermsUsersModel::factory()->create(
            [
                'educational_program_payment_term_uid' => $paymentTerm->uid,
                'user_uid' => $user->uid
            ]
        );

        EducationalProgramsPaymentTermsUsersModel::factory()->create(
            [
                'educational_program_payment_term_uid' => $paymentTerm1->uid,
                'user_uid' => $user1->uid
            ]
        );

        // Crear una solicitud simulada con búsqueda
        $requestData = [
            'items_per_page' => 2,
            'search' => $educationalProgram->name // Buscar por nombre del primer programa
        ];
        // Hacer la solicitud POST a la ruta de obtener programas formativos matriculados
        $response = $this->post(route('my-educational-programs-enrolled-get'), $requestData);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que solo el programa que coincide con la búsqueda se devuelve
        $response->assertJsonFragment([
            'uid' => $educationalProgram->uid,
        ]);

        // Asegurarse de que no se devuelven otros programas
        $response->assertJsonMissing([
            'uid' => $educationalProgram->uid,
        ]);
    }

    /**
     * @test
     * Prueba que el curso es accesible cuando el programa formativo está en desarrollo
     */
    public function testAccessCourseWhenProgramIsInDevelopment()
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

        $status = EducationalProgramStatusesModel::where('code', 'DEVELOPMENT')->first();

        // Crear un programa formativo con estado 'DEVELOPMENT'
        $program = EducationalProgramsModel::factory()
            ->withEducationalProgramType()
            ->create([
                'educational_program_status_uid' => $status->uid
            ])->first();


        // Crear un curso asociado al programa
        $course = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create([
                'educational_program_uid' => $program->uid,
                'lms_url' => 'https://lms.example.com/course/123'
            ])->first();

        CoursesAccessesModel::factory()->create(
            [
                "course_uid" => $course->uid,
                "user_uid" => $user->uid,
            ]
        );

        // Crear una solicitud simulada para acceder al curso
        $requestData = [
            'courseUid' => $course->uid
        ];

        // Hacer la solicitud POST a la ruta de acceso al curso
        $response = $this->post(route('my-educational-programs-enrolled-access-course'), $requestData);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que el LMS URL se devuelve en el JSON
        $response->assertJsonFragment([
            'lmsUrl' => $course->lms_url
        ]);

        // Verificar que el acceso al curso ha sido registrado
        $this->assertDatabaseHas('courses_accesses', [
            'course_uid' => $course->uid,
            'user_uid' => $user->uid
        ]);
    }

    /**
     * @test
     * Prueba que se lanza una excepción si el programa no está en desarrollo
     */
    public function testAccessCourseThrowsExceptionIfProgramNotInDevelopment()
    {
        $this->withoutExceptionHandling();

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

        // Buscar un programa formativo con estado diferente a 'DEVELOPMENT'
        $status = EducationalProgramStatusesModel::where('code', 'INSCRIPTION')->first();

        // Crear un programa formativo con estado diferente a 'DEVELOPMENT'
        $program = EducationalProgramsModel::factory()
            ->withEducationalProgramType()
            ->create([
                'educational_program_status_uid' => $status->uid
            ])->first();


        // Crear un curso asociado al programa
        $course = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create([
                'educational_program_uid' => $program->uid
            ])->first();

        // Crear una solicitud simulada para acceder al curso
        $requestData = [
            'courseUid' => $course->uid
        ];

        // Esperar que se lance una excepción
        $this->expectException(OperationFailedException::class);
        $this->expectExceptionMessage("El programa formativo no se encuentra en desarrollo");

        // Hacer la solicitud POST a la ruta de acceso al curso
        $this->post(route('my-educational-programs-enrolled-access-course'), $requestData);
    }

    /**
     * @test
     * Prueba que se lanza una excepción si el programa no está en desarrollo
     */
    public function testPayTermWithPaymentTermUser()
    {
        // Buscamos un usuario
        $user = UsersModel::factory()->create();
        // Lo autenticarlo
        $this->actingAs($user);

        $educational = EducationalProgramsModel::factory()->withEducationalProgramType()->create();

        $paymentTerm = EducationalProgramsPaymentTermsModel::factory()->create(
            [
                'cost' => 100,
                'educational_program_uid' => $educational->uid
            ]
        );

        EducationalProgramsPaymentTermsUsersModel::factory()->create(
            [
                'educational_program_payment_term_uid' => $paymentTerm->uid,
                'user_uid' => $user->uid,
            ]
        );

        $general_options = [
            'payment_gateway' => true,
            'redsys_commerce_code' => true,
            'redsys_currency' => true,
            'redsys_transaction_type' => false,
            'redsys_terminal' => true,
            'redsys_encryption_key' => true,
        ];

        app()->instance('general_options', $general_options);


        $data = [
            'paymentTermUid' => $paymentTerm->uid
        ];

        // Hacer la solicitud POST a la ruta de acceso al curso
        $response = $this->post(route('my-educational-programs-enrolled-pay-term'), $data);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);
    }

    /**
     * @test
     *
     */
    public function testPayTermWithEducationalProgramsPaymentTerms()
    {
        // Buscamos un usuario
        $user = UsersModel::factory()->create();
        // Lo autenticarlo
        $this->actingAs($user);

        $educational = EducationalProgramsModel::factory()->withEducationalProgramType()->create();

        $paymentTerm = EducationalProgramsPaymentTermsModel::factory()->create(
            [
                'cost' => 100,
                'educational_program_uid' => $educational->uid
            ]
        );

        $general_options = [
            'payment_gateway' => true,
            'redsys_commerce_code' => true,
            'redsys_currency' => true,
            'redsys_transaction_type' => false,
            'redsys_terminal' => true,
            'redsys_encryption_key' => true,
        ];

        app()->instance('general_options', $general_options);


        $data = [
            'paymentTermUid' => $paymentTerm->uid
        ];

        // Hacer la solicitud POST a la ruta de acceso al curso
        $response = $this->post(route('my-educational-programs-enrolled-pay-term'), $data);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);
    }

    /**
     * @test
     *
     */
    public function testPayTermWithEFail406()
    {
        // Buscamos un usuario
        $user = UsersModel::factory()->create();
        // Lo autenticarlo
        $this->actingAs($user);

        $educational = EducationalProgramsModel::factory()->withEducationalProgramType()->create();

        $paymentTerm = EducationalProgramsPaymentTermsModel::factory()->create(
            [
                'start_date' => Carbon::now()->addDays(10),
                'finish_date' => Carbon::yesterday(),
                'educational_program_uid' => $educational->uid
            ]
        );

        $data = [
            'paymentTermUid' => $paymentTerm->uid
        ];

        $response = $this->post(route('my-educational-programs-enrolled-pay-term'), $data);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(406);

        $response->assertJson(['message' => 'El plazo de pago no está activo']);
    }
}
