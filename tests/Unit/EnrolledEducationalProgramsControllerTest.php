<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\CoursesModel;
use App\Models\CoursesAccessesModel;
use App\Models\EducationalProgramsModel;
use App\Exceptions\OperationFailedException;
use App\Models\EducationalProgramStatusesModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EducationalProgramsPaymentTermsModel;
use App\Models\EducationalProgramsPaymentTermsUsersModel;

class EnrolledEducationalProgramsControllerTest extends TestCase
{
    use RefreshDatabase;

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

        // $educationalPrograms = EducationalProgramsModel::get();

        foreach ($educationalPrograms as $educationalProgram) {

            $user->educationalPrograms()->attach($educationalProgram->uid, [
                'uid' => generate_uuid(),
                'status' => 'ENROLLED',
                'acceptance_status' => 'ACCEPTED'
            ]);
        }

        CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create([
                'educational_program_uid' => $educationalPrograms[0]->uid,
            ]);


        // Crear un término de pago
        $paymentTerm = EducationalProgramsPaymentTermsModel::factory()->create([
            'educational_program_uid' => $educationalPrograms[0]->uid,
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

        // dd($paymentTermsUser);

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

        $status = EducationalProgramStatusesModel::where('code', 'DEVELOPMENT')->first();

        // Crear algunos programas formativos y matricular al usuario
        EducationalProgramsModel::factory()
            ->withEducationalProgramType()
            ->count(3)->create(
                [
                    'educational_program_status_uid' => $status->uid
                ]
            );

        $educationalPrograms = EducationalProgramsModel::all();

        foreach ($educationalPrograms as $educationalProgram) {

            $user->educationalPrograms()->attach($educationalProgram->uid, [
                'uid' => generate_uuid(),
                'status' => 'ENROLLED',
                'acceptance_status' => 'ACCEPTED'
            ]);
        }

        CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create([
                'educational_program_uid' => $educationalPrograms[0]->uid,
            ]);

        $paymentTerm = EducationalProgramsPaymentTermsModel::factory()->create(
            [
                'educational_program_uid' => $educationalPrograms[0]->uid
            ]
        )->first();

        EducationalProgramsPaymentTermsUsersModel::factory()->create(
            [
                'educational_program_payment_term_uid' => $paymentTerm->uid,
                'user_uid' => $user->uid
            ]
        );
        // Crear una solicitud simulada con búsqueda
        $requestData = [
            'items_per_page' => 2,
            'search' => $educationalPrograms[0]->name // Buscar por nombre del primer programa
        ];
        // Hacer la solicitud POST a la ruta de obtener programas formativos matriculados
        $response = $this->post(route('my-educational-programs-enrolled-get'), $requestData);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que solo el programa que coincide con la búsqueda se devuelve
        $response->assertJsonFragment([
            'uid' => $educationalPrograms[0]->uid,
        ]);

        // Asegurarse de que no se devuelven otros programas
        $response->assertJsonMissing([
            'uid' => $educationalPrograms[1]->uid,
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
}
