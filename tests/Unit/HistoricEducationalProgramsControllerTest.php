<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\CoursesModel;
use App\Models\EducationalProgramsModel;
use App\Exceptions\OperationFailedException;
use App\Models\EducationalProgramStatusesModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HistoricEducationalProgramsControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Prueba que la vista del histórico de programas formativos se carga correctamente con los datos necesarios
     */
    public function testIndexLoadsHistoricEducationalProgramsPageWithCorrectData()
    {
        // Buscamos un usuario  
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        // Si no existe el usuario lo creamos
        if (!$user) {
            $user = UsersModel::factory()->create([
                'email'=>'admin@admin.com'
            ])->first();
        }
        // Lo autenticarlo         
        $this->actingAs($user);

        // Hacer una solicitud GET a la ruta del histórico de programas formativos
        $response = $this->get(route('my-educational-programs-historic'));

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que la vista correcta se cargue
        $response->assertViewIs('profile.my_educational_programs.historic_educational_programs.index');

        // Verificar que los recursos JavaScript y el título de la página se pasan correctamente
        $response->assertViewHas('resources', ['resources/js/profile/my_educational_programs/historic_educational_programs.js']);
        $response->assertViewHas('page_title', 'Histórico de programas formativos');
        $response->assertViewHas('currentPage', 'historicEducationalPrograms');
    }


    /**
     * @test
     * Prueba que los programas formativos históricos se devuelven correctamente con paginación
     */
    public function testGetHistoricEducationalProgramsReturnsCorrectData()
    {
       
        // Buscamos un usuario  
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        // Si no existe el usuario lo creamos
        if (!$user) {
            $user = UsersModel::factory()->create([
                'email'=>'admin@admin.com'
            ])->first();
        }
        // Lo autenticarlo         
        $this->actingAs($user);


        $status = EducationalProgramStatusesModel::where('code', 'FINISHED')->first();

        // Crear algunos programas formativos y marcarlos como 'FINISHED'
        EducationalProgramsModel::factory()
            ->withEducationalProgramType()
            ->count(3)
            ->create(
                [
                    'educational_program_status_uid' => $status->uid
                ]
            );

        $educationalPrograms = EducationalProgramsModel::get();

        foreach ($educationalPrograms as $key => $educationalProgram) {

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
        }

        // CoursesModel::factory()
        //     ->withCourseStatus()
        //     ->withCourseType()
        //     ->create([
        //         'educational_program_uid' => $educationalPrograms[0]->uid,
        //     ]);

        // Crear una solicitud simulada con paginación
        $requestData = [
            'items_per_page' => 2,
            'search' => null
        ];

        // Hacer la solicitud POST a la ruta de obtener programas formativos históricos
        $response = $this->post(route('my-educational-programs-historic-get'), $requestData);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que los programas formativos históricos se devuelven correctamente
        $response->assertJsonFragment([
            'uid' => $educationalPrograms[0]->uid,
        ]);
    }

    /**
     * @test
     * Prueba que la búsqueda de programas formativos históricos funciona correctamente
     */
    public function testGetHistoricEducationalProgramsSearchWorks()
    {
        // Buscamos un usuario  
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        // Si no existe el usuario lo creamos
        if (!$user) {
            $user = UsersModel::factory()->create([
                'email'=>'admin@admin.com'
            ])->first();
        }
        // Lo autenticarlo         
        $this->actingAs($user);

        $status = EducationalProgramStatusesModel::where('code', 'FINISHED')->first();

        // Crear algunos programas formativos y marcarlos como 'FINISHED'
        EducationalProgramsModel::factory()
            ->withEducationalProgramType()
            ->count(3)
            ->create(
                [
                    'educational_program_status_uid' => $status->uid
                ]
            );

        $educationalPrograms = EducationalProgramsModel::get();

        foreach ($educationalPrograms as $key => $educationalProgram) {
            // Crear algunos programas formativos y marcarlos como 'FINISHED'
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
        }

        // Crear una solicitud simulada con búsqueda
        $requestData = [
            'items_per_page' => 2,
            'search' => $educationalPrograms[0]->name // Buscar por nombre del primer programa
        ];

        // Hacer la solicitud POST a la ruta de obtener programas formativos históricos
        $response = $this->post(route('my-educational-programs-historic-get'), $requestData);

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
     * Prueba que el curso es accesible cuando el programa formativo está finalizado
     */
    public function testAccessCourseWhenProgramIsFinished()
    {
        // Buscamos un usuario  
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        // Si no existe el usuario lo creamos
        if (!$user) {
            $user = UsersModel::factory()->create([
                'email'=>'admin@admin.com'
            ])->first();
        }
        // Lo autenticarlo         
        $this->actingAs($user);

        $status = EducationalProgramStatusesModel::where('code', 'FINISHED')->first();

        // Crear un programa formativo con estado 'FINISHED'
        EducationalProgramsModel::factory()
            ->withEducationalProgramType()
            ->create(
                [
                    'educational_program_status_uid' => $status->uid
                ]
            );

        $educationalProgram = EducationalProgramsModel::first();

        // Crear un curso asociado al programa
        $course = CoursesModel::factory()
        ->withCourseStatus()
        ->withCourseType()
        ->create([
            'educational_program_uid' => $educationalProgram->uid,
            'lms_url' => 'https://lms.example.com/course/123'
        ])->first();

        // Crear una solicitud simulada para acceder al curso
        $requestData = [
            'courseUid' => $course->uid
        ];

        // Hacer la solicitud POST a la ruta de acceso al curso
        $response = $this->post(route('my-educational-programs-historic-access-course'), $requestData);

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
     * Prueba que se lanza una excepción si el programa no está finalizado
     */
    public function testAccessCourseThrowsExceptionIfProgramNotFinished()
    {
        $this->withoutExceptionHandling();

        // Buscamos un usuario  
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        // Si no existe el usuario lo creamos
        if (!$user) {
            $user = UsersModel::factory()->create([
                'email'=>'admin@admin.com'
            ])->first();
        }
        // Lo autenticarlo         
        $this->actingAs($user);

        $status = EducationalProgramStatusesModel::where('code', 'DEVELOPMENT')->first();

       // Crear un programa formativo con estado diferente a 'FINISHED'
        EducationalProgramsModel::factory()
            ->withEducationalProgramType()
            ->create(
                [
                    'educational_program_status_uid' => $status->uid
                ]
            );

        $educationalProgram = EducationalProgramsModel::first();

        // Crear un curso asociado al programa
        $course = CoursesModel::factory()
        ->withCourseStatus()
        ->withCourseType()->create([
            'educational_program_uid' => $educationalProgram->uid
        ])->first();

        // Crear una solicitud simulada para acceder al curso
        $requestData = [
            'courseUid' => $course->uid
        ];

        // Esperar que se lance una excepción
        $this->expectException(OperationFailedException::class);
        $this->expectExceptionMessage("El programa formativo no se encuentra finalizado");

        // Hacer la solicitud POST a la ruta de acceso al curso
        $this->post(route('my-educational-programs-historic-access-course'), $requestData);
    }
}
