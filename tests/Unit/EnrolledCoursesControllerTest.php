<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\CoursesModel;
use App\Models\CoursesPaymentTermsModel;
use App\Models\EducationalProgramsModel;
use App\Exceptions\OperationFailedException;
use App\Models\EducationalProgramStatusesModel;
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
            'status' => 'ENROLLED'
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

        // Verificar que los programas educativos matriculados se devuelven correctamente
        // $response->assertJsonFragment([
        //     'uid' => $educationalPrograms[0]->uid,
        // ]);
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
        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
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

        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
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

        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
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
}
