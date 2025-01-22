<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\CoursesModel;
use App\Models\CourseStatusesModel;
use App\Models\GeneralOptionsModel;
use App\Models\CoursesPaymentTermsModel;
use App\Models\EducationalProgramsModel;
use App\Exceptions\OperationFailedException;
use App\Models\CoursesPaymentTermsUsersModel;
use App\Models\EducationalProgramStatusesModel;
use Database\Factories\GeneralOptionsModelFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EnrolledCoursesControllerTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     * Prueba que la vista de cursos matriculados se carga correctamente con los datos necesarios
     */
    public function testIndexLoadsEnrolledCoursesPageWithCorrectData()
    {
        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        // Hacer una solicitud GET a la ruta de cursos matriculados
        $response = $this->get(route('my-courses-enrolled'));

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que la vista correcta se cargue
        $response->assertViewIs('profile.my_courses.enrolled_courses.index');

        // Verificar que los recursos JavaScript y el título de la página se pasan correctamente
        $response->assertViewHas('resources', ['resources/js/profile/my_courses/enrolled_courses.js']);
        $response->assertViewHas('page_title', 'Mis cursos matriculados');
        $response->assertViewHas('currentPage', 'enrolledCourses');
    }


    /**
     * @test
     * Prueba que los programas educativos matriculados se recuperan correctamente con paginación
     */
    public function testGetEnrolledEducationalProgramsReturnsCorrectData()
    {
        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        // Crear algunos programas educativos y matricular al usuario
        EducationalProgramsModel::factory()
            ->withEducationalProgramType()
            ->count(3)
            ->create();

        $educationalPrograms = EducationalProgramsModel::all();

        $user->educationalPrograms()->attach($educationalPrograms[0]->uid, [
            'uid' => generate_uuid(),
            'status' => 'ENROLLED',
            'acceptance_status' => 'ACCEPTED'
        ]);

        // Verificar que la relación se ha creado correctamente
        $this->assertDatabaseHas('educational_programs_students', [
            'user_uid' => $user->uid,
            'educational_program_uid' => $educationalPrograms[0]->uid,
            'status' => 'ENROLLED'
        ]);

        // Crear una solicitud simulada con paginación
        $requestData = [
            'items_per_page' => 2,
            'search' => null
        ];

        // Hacer la solicitud POST a la ruta de obtener programas educativos matriculados
        $response = $this->post(route('my-educational-programs-enrolled-get'), $requestData);

        // $responseData = $response->json();
        // dd($responseData);  // Depuración para verificar los datos devueltos

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);
        
    }

    /**
     * @test
     * Prueba que la búsqueda de programas educativos matriculados funciona correctamente
     */
    // public function testGetEnrolledEducationalProgramsSearchWorks()
    // {
    //     // Crear un usuario y autenticarlo
    //     $user = UsersModel::factory()->create();
    //     $this->actingAs($user);

    //     // Crear algunos programas educativos y matricular al usuario
    //     EducationalProgramsModel::factory()
    //         ->withEducationalProgramType()
    //         ->count(3)
    //         ->create();

    //     $educationalPrograms = EducationalProgramsModel::all();

    //     $user->educationalPrograms()->attach($educationalPrograms[0]->uid, [
    //         'uid' => generate_uuid(),
    //         'status' => 'ENROLLED'
    //     ]);

    //     // Crear una solicitud simulada con búsqueda
    //     $requestData = [
    //         'items_per_page' => 2,
    //         'search' => $educationalPrograms[0]->name // Buscar por nombre del primer programa
    //     ];

    //     // Hacer la solicitud POST a la ruta de obtener programas educativos matriculados
    //     $response = $this->post(route('my-educational-programs-enrolled-get'), $requestData);

    //     // Verificar que la respuesta es exitosa
    //     $response->assertStatus(200);

    //     // Verificar que solo el programa que coincide con la búsqueda se devuelve
    //     $response->assertJsonFragment([
    //         'uid' => $educationalPrograms[0]->uid,
    //     ]);

    //     // Asegurarse de que no se devuelven otros programas
    //     $response->assertJsonMissing([
    //         'uid' => $educationalPrograms[1]->uid,
    //     ]);
    // }

    /**
     * @test
     * Prueba que el curso es accesible cuando el programa educativo está en desarrollo
     */
    public function testAccessCourseWhenProgramIsInDevelopment()
    {
        // Buscar un usuario y autenticarlo
        $user = UsersModel::where('email', 'admin@admin.com')->first();

        $this->actingAs($user);

        $status = EducationalProgramStatusesModel::where('code', 'DEVELOPMENT')->first();

        // Crear un programa educativo con estado 'DEVELOPMENT'
        $program = EducationalProgramsModel::factory()
            ->withEducationalProgramType()
            ->create(
                ['educational_program_status_uid' => $status->uid]
            )->first();

        // Crear un curso asociado al programa
        $course = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create([
                'educational_program_uid' => $program->uid,
                'lms_url' => 'https://lms.example.com/course/123'
            ])->first();

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

        // Buscar un usuario y autenticarlo          
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        //Si no existe el usuario lo creamos
        if (!$user) {
            $user = UsersModel::factory()->create([
                'email' => 'admin@admin.com'
            ])->first();
        }

        $this->actingAs($user);

        // Busco un programa educativo con estado diferente a 'DEVELOPMENT'
        $status = EducationalProgramStatusesModel::where('code', 'INSCRIPTION')->first();

        // Crear un programa educativo con estado diferente a 'DEVELOPMENT'
        $program = EducationalProgramsModel::factory()
            ->withEducationalProgramType()
            ->create(
                ['educational_program_status_uid' => $status->uid]
            )->first();

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
     * Prueba que se lanza una excepción si el plazo de pago no está activo
     */
    public function testPayTermThrowsExceptionWhenTermNotActive()
    {
        $this->withoutExceptionHandling();

        // Buscar un usuario      
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        //Si no existe el usuario lo creamos
        if (!$user) {
            $user = UsersModel::factory()->create([
                'email' => 'admin@admin.com'
            ])->first();
        }
        // Lo autenticarlo    
        $this->actingAs($user);

        // Crear un curso
        $course = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create()
            ->first();

        // Crear un plazo de pago inactivo (fuera de rango de fechas)
        $paymentTerm = CoursesPaymentTermsModel::factory()->create([
            'course_uid' => $course->uid,
            'start_date' => now()->subDays(10), // Expiró hace 10 días
            'finish_date' => now()->subDays(5), // Terminó hace 5 días
        ])->first();

        // Crear una solicitud simulada para el plazo de pago
        $requestData = [
            'paymentTermUid' => $paymentTerm->uid
        ];

        // Esperar que se lance una excepción
        $this->expectException(OperationFailedException::class);
        $this->expectExceptionMessage("El plazo de pago no está activo");

        // Hacer la solicitud POST a la ruta de pagar plazo de pago
        $this->post(route('enrolled-courses-pay-term'), $requestData);
    }

    /**
     * @test
     * Prueba la obtención de los cursos en los que el usuario está inscrito.
     */
    public function testGetEnrolledCoursesReturnsCorrectData()
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

        // Crear algunos cursos de prueba con estados y términos de pago
        $status = CourseStatusesModel::where('code', 'ENROLLING')->first();


        CoursesModel::factory()
            ->withCourseType()
            ->create(['course_status_uid' => $status->uid, 'title' => 'Curso de prueba 1']);

        CoursesModel::factory()
            ->withCourseType()
            ->create(['course_status_uid' => $status->uid, 'title' => 'Curso de prueba 2']);

        $courses = CoursesModel::get();

        // Simular que el usuario está inscrito en estos cursos
        foreach ($courses as $key => $course) {
            $user->courses_students()->attach($course->uid, [
                'uid' => generate_uuid(),
                'status' => 'ENROLLED'
            ]);

            // Crear términos de pago y asignar pagos de usuario para los cursos
            CoursesPaymentTermsModel::factory()->create(
                ['course_uid' => $course->uid]
            )->first();
        }

        $paymentTerm = CoursesPaymentTermsModel::first();

        CoursesPaymentTermsUsersModel::factory()->create([
            'course_payment_term_uid' => $paymentTerm->uid,
            'user_uid' => $user->uid,
        ]);

        // Simular la solicitud con paginación y búsqueda vacía
        $requestData = [
            'items_per_page' => 10,
            'search' => null,
        ];

        // Hacer la solicitud POST a la ruta de obtener cursos inscritos
        $response = $this->post(route('get-enrolled-courses'), $requestData);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que los cursos inscritos se devuelven correctamente en la respuesta JSON
        $response->assertJsonFragment([
            'title' => 'Curso de prueba 1',
        ]);
        $response->assertJsonFragment([
            'title' => 'Curso de prueba 2',
        ]);
    }


    /**
     * @test
     * Prueba la obtención de los cursos en los que el usuario está inscrito con la estructura correcta.
     */
    public function testGetEnrolledCoursesReturnsCorrectDataWithUpdatedStructure()
    {
        // Buscar o crear el usuario
        $user = UsersModel::where('email', 'admin@admin.com')->first() ?? UsersModel::factory()->create(['email' => 'admin@admin.com']);

        // Autenticar al usuario
        $this->actingAs($user);

        // Crear estado de curso
        $status = CourseStatusesModel::where('code', 'ENROLLING')->first() ?? CourseStatusesModel::factory()->create(['code' => 'ENROLLING']);

        // Crear cursos de prueba asociados al estado
        $courses = CoursesModel::factory()->count(2)->withCourseType()->create(['course_status_uid' => $status->uid]);

        // Simular que el usuario está inscrito en estos cursos y crear términos de pago
        foreach ($courses as $course) {
            $user->courses_students()->attach($course->uid, ['uid' => generate_uuid(), 'status' => 'ENROLLED']);

            // Crear términos de pago y asociarlos al curso
            $paymentTerm = CoursesPaymentTermsModel::factory()->create(['course_uid' => $course->uid]);

            // Crear pago del usuario para el término de pago y agregar en forma de colección
            CoursesPaymentTermsUsersModel::factory()->create([
                'course_payment_term_uid' => $paymentTerm->uid,
                'user_uid' => $user->uid,
                'is_paid' => true,
            ]);
        }

        // Simular la solicitud con paginación y búsqueda vacía
        $requestData = [
            'items_per_page' => 10,
            'search' => $courses[0]->title, 
        ];

        // Hacer la solicitud POST a la ruta de obtener cursos inscritos
        $response = $this->postJson(route('get-enrolled-courses'), $requestData);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);       

        // Verificar que los títulos de los cursos están presentes en la respuesta JSON
        $response->assertJsonFragment(['title' => $courses[0]->title]);
        // $response->assertJsonFragment(['title' => $courses[1]->title]);
    }



    /**
     * @test
     * Prueba el acceso a un curso cuando el estado del curso es 'DEVELOPMENT'.
     */
    public function testAccessCourseWhenInDevelopment()
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


        // Buscar el estado 'DEVELOPMENT' y un curso asociado
        $status = CourseStatusesModel::where('code', 'DEVELOPMENT')->first();
        $course = CoursesModel::factory()
            ->withCourseType()
            ->create(['course_status_uid' => $status->uid, 'lms_url' => 'https://lms.example.com/course/123']);

        // Simular la solicitud para acceder al curso
        $response = $this->post(route('enrolled-courses-access'), ['courseUid' => $course->uid]);

        // Verificar que la respuesta es exitosa y devuelve la URL correcta del LMS
        $response->assertStatus(200);
        $response->assertJson([
            'lmsUrl' => 'https://lms.example.com/course/123'
        ]);

        // Verificar que se ha registrado el acceso al curso en la base de datos
        $this->assertDatabaseHas('courses_accesses', [
            'course_uid' => $course->uid,
            'user_uid' => $user->uid,
        ]);
    }

    /**
     * @test
     * Prueba que el acceso a un curso es rechazado si el estado no es 'DEVELOPMENT'.
     */
    public function testAccessCourseWhenNotInDevelopment()
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

        // Buscar un estado diferente a 'DEVELOPMENT' y un curso asociado

        $status = CourseStatusesModel::where('code', 'ENROLLING')->first();
        $course = CoursesModel::factory()
            ->withCourseType()
            ->create(['course_status_uid' => $status->uid])->first();

        // Simular la solicitud para acceder al curso
        $response = $this->post(route('enrolled-courses-access'), ['courseUid' => $course->uid]);

        // Verificar que la respuesta es un error 403
        $response->assertStatus(403);

        // Verificar que no se ha registrado el acceso al curso en la base de datos
        $this->assertDatabaseMissing('courses_accesses', [
            'course_uid' => $course->uid,
            'user_uid' => $user->uid,
        ]);
    }

    /**
     * @test
     * Prueba el pago de un plazo cuando el plazo está activo.
     */
    public function testPayTermWhenPaymentTermIsActive()
    {
        // Crear un usuario de prueba y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        $course = CoursesModel::factory()
        ->withCourseType()
        ->withCourseStatus()
        ->create(); 

        // Crear un plazo de pago activo
        $paymentTerm = CoursesPaymentTermsModel::factory()->create([
            'start_date' => now()->subDay(),
            'finish_date' => now()->addDay(),
            'course_uid' => $course->uid,
        ]);

        CoursesPaymentTermsUsersModel::factory()->create(
            [
                'course_payment_term_uid'=> $paymentTerm->uid,
                'user_uid' => $user->uid
            ]
        );

        $general = GeneralOptionsModel::where('option_name','payment_gateway')->first();
        $general->option_value = true;
        $general->save();      


        // Simular la solicitud para pagar el plazo
        $response = $this->post(route('enrolled-courses-pay-term'), ['paymentTermUid' => $paymentTerm->uid]);

        // Verificar que la respuesta es exitosa y contiene los parámetros de redsys
        $response->assertStatus(200);       

        // Verificar que se ha creado o recuperado el registro del usuario para este plazo
        $this->assertDatabaseHas('courses_payment_terms_users', [
            'course_payment_term_uid' => $paymentTerm->uid,
            'user_uid' => $user->uid,
        ]);
    }

    /**
     * @test
     * Prueba el pago de un plazo cuando el plazo está activo.
     */
    public function testPayTermWhenPaymentTermIsActiveWithoutCoursesPaymentTermsUsers()
    {
        // Crear un usuario de prueba y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        $course = CoursesModel::factory()
        ->withCourseType()
        ->withCourseStatus()
        ->create(); 

        // Crear un plazo de pago activo
        $paymentTerm = CoursesPaymentTermsModel::factory()->create([
            'start_date' => now()->subDay(),
            'finish_date' => now()->addDay(),
            'course_uid' => $course->uid,
        ]);       

        $general = GeneralOptionsModel::where('option_name','payment_gateway')->first();
        $general->option_value = true;
        $general->save();      


        // Simular la solicitud para pagar el plazo
        $response = $this->post(route('enrolled-courses-pay-term'), ['paymentTermUid' => $paymentTerm->uid]);

        // Verificar que la respuesta es exitosa y contiene los parámetros de redsys
        $response->assertStatus(200);    
       
    }

    /**
     * @test
     * Prueba que se lanza una excepción cuando el plazo de pago no está activo.
     */
    public function testPayTermWhenPaymentTermIsNotActive()
    {
        // Crear un usuario de prueba y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        $course = CoursesModel::factory()
        ->withCourseType()
        ->withCourseStatus()
        ->create(); 

        // Crear un plazo de pago inactivo (terminado)
        $paymentTerm = CoursesPaymentTermsModel::factory()->create([
            'start_date' => now()->subDays(10),
            'finish_date' => now()->subDays(5),
            'course_uid' => $course->uid,
        ]);

        // Simular la solicitud para pagar el plazo
        $response = $this->post(route('enrolled-courses-pay-term'), ['paymentTermUid' => $paymentTerm->uid]);
       
        $response->assertStatus(406);
        $response->assertJson([
            'message'=>'El plazo de pago no está activo',
         ]);

    }
}
