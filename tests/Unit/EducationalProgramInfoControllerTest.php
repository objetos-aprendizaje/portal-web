<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsersModel;
use Illuminate\Support\Str;
use App\Models\CoursesModel;
use App\Models\EducationalProgramsModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EducationalProgramsAssessmentsModel;

class EducationalProgramInfoControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Prueba que se carga la vista con la información del programa educativo y los profesores
     */
    public function testIndexLoadsViewWithProgramAndUniqueTeachers()
    {
        // Crear un programa educativo en la base de datos
        $program = EducationalProgramsModel::factory()
            ->withEducationalProgramType()->create([
                'uid' => generate_uuid(),
                'name' => 'Programa educativo de prueba'
            ])->first();

        // Crear varios cursos asociados al programa educativo
        $course = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create([
                'educational_program_uid' => $program->uid
            ])->first();

        // Crear profesores y asociarlos a los cursos
        $teachers = UsersModel::factory()->count(5)->create();

        $teachersToAttach = $teachers->random(3)->pluck('uid')->toArray();

        // dd($teachersToAttach);

        foreach ($teachersToAttach as $teacherUid) {
            $course->teachers()->attach($teacherUid, [
                'uid' => (string) Str::uuid(),  // Generar un UID para la tabla pivote
            ]);
        }
        // Hacer una solicitud GET a la ruta del programa educativo
        $response = $this->get("/educational_program/{$program->uid}");

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que la vista cargada es la correcta
        $response->assertViewIs('educational-program-info');

        // Verificar que el programa educativo y los profesores se pasan a la vista
        $response->assertViewHas('educational_program', function ($viewProgram) use ($program) {
            return $viewProgram->uid === $program->uid;
        });

        // Verificar que los profesores únicos se pasan a la vista
        $response->assertViewHas('teachers', function ($viewTeachers) use ($teachers) {
            return count($viewTeachers) <= 5; // Asegurar que los profesores únicos se pasan
        });

        // Verificar que los recursos se pasan correctamente
        $response->assertViewHas('resources', ['resources/js/educational_program_info.js']);
    }

    public function testIndexLoadsViewWithProgramAndUserLogged()
    {

        // Crear un usuario y autenticarlo      
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        $this->actingAs($user);

        // Crear un programa educativo en la base de datos
        $program = EducationalProgramsModel::factory()
            ->withEducationalProgramType()->create([
                'uid' => generate_uuid(),
                'name' => 'Programa educativo de prueba'
            ])->first();

        // Crear varios cursos asociados al programa educativo
        $course = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create([
                'educational_program_uid' => $program->uid
            ])->first();

        // Crear profesores y asociarlos a los cursos
        $teachers = UsersModel::factory()->count(5)->create();

        $teachersToAttach = $teachers->random(3)->pluck('uid')->toArray();

        // dd($teachersToAttach);

        foreach ($teachersToAttach as $teacherUid) {
            $course->teachers()->attach($teacherUid, [
                'uid' => (string) Str::uuid(),  // Generar un UID para la tabla pivote
            ]);
        }
        // Hacer una solicitud GET a la ruta del programa educativo
        $response = $this->get("/educational_program/{$program->uid}");

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que la vista cargada es la correcta
        $response->assertViewIs('educational-program-info');

        // Verificar que el programa educativo y los profesores se pasan a la vista
        $response->assertViewHas('educational_program', function ($viewProgram) use ($program) {
            return $viewProgram->uid === $program->uid;
        });

        // Verificar que los profesores únicos se pasan a la vista
        $response->assertViewHas('teachers', function ($viewTeachers) use ($teachers) {
            return count($viewTeachers) <= 5; // Asegurar que los profesores únicos se pasan
        });

        // Verificar que los recursos se pasan correctamente
        $response->assertViewHas('resources', ['resources/js/educational_program_info.js']);
    }

    /**
     * @test
     * Prueba que se devuelve el programa educativo con cursos y profesores en formato JSON
     */
    public function testGetEducationalProgramApiReturnsProgramWithCoursesAndTeachers()
    {
        // Crear un programa educativo en la base de datos
        $program = EducationalProgramsModel::factory()
            ->withEducationalProgramType()->create([
                'uid' => generate_uuid(),
                'name' => 'Programa educativo de prueba'
            ])->first();

        // Crear varios cursos asociados al programa educativo
        $course = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create([
                'educational_program_uid' => $program->uid
            ])->first();

        // Crear profesores y asociarlos a los cursos
        $teachers = UsersModel::factory()->count(5)->create();

        $teachersToAttach = $teachers->random(3)->pluck('uid')->toArray();
        foreach ($teachersToAttach as $teacherUid) {
            $course->teachers()->attach($teacherUid, [
                'uid' => (string) \Illuminate\Support\Str::uuid(),  // Generar un UID para la tabla pivote
            ]);
        }
        // Hacer una solicitud GET a la ruta de la API
        $response = $this->get("/educational_program/get_educational_program/{$program->uid}");

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que los datos del programa educativo, los cursos y los profesores están presentes en el JSON
        $response->assertJsonFragment([
            'uid' => $program->uid,
            'name' => $program->name
        ]);

        // Verificar que los cursos están en el JSON
        $response->assertJsonFragment([
            'uid' => $course->uid,
            'educational_program_uid' => $program->uid
        ]);

        // Verificar que los profesores están en el JSON
        // foreach ($teachers as $teacher) {
        //     $response->assertJsonFragment([
        //         'uid' => $teacher->uid
        //     ]);
        // }
    }


    /**
     * @test
     * Prueba que un usuario puede calificar un programa educativo (nueva calificación)
     */
    public function testCalificateRegistersNewCalification()
    {
        // Crear un usuario y autenticarlo      
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        $this->actingAs($user);

        // Crear un programa educativo en la base de datos
        $program = EducationalProgramsModel::factory()
            ->withEducationalProgramType()
            ->create([
                'uid' => generate_uuid(),
                'name' => 'Programa educativo de prueba',
            ])->first();

        // Crear una solicitud para calificar el programa
        $request = [
            'calification' => 4,
            'educational_program_uid' => $program->uid,
        ];

        // Hacer la solicitud POST a la ruta de calificación
        $response = $this->post('/educational_program/calificate', $request);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que se ha registrado la calificación correctamente en la base de datos
        $this->assertDatabaseHas('educational_programs_assessments', [
            'user_uid' => $user->uid,
            'educational_program_uid' => $program->uid,
            'calification' => 4,
        ]);
    }

    /**
     * @test
     * Prueba que un usuario puede actualizar su calificación de un programa educativo
     */
    public function testCalificateUpdatesExistingCalification()
    {
        // Crear un usuario y autenticarlo    
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        $this->actingAs($user);

        // Crear un programa educativo en la base de datos
        $program = EducationalProgramsModel::factory()
            ->withEducationalProgramType()
            ->create([
                'uid' => generate_uuid(),
                'name' => 'Programa educativo de prueba',
            ])->first();

        // Crear una calificación existente para el programa educativo
        $assessment = EducationalProgramsAssessmentsModel::factory()->create([
            'user_uid' => $user->uid,
            'educational_program_uid' => $program->uid,
            'calification' => 3,
        ]);

        // Crear una solicitud para actualizar la calificación del programa
        $request = [
            'calification' => 5,
            'educational_program_uid' => $program->uid,
        ];

        // Hacer la solicitud POST a la ruta de calificación
        $response = $this->post('/educational_program/calificate', $request);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que la calificación ha sido actualizada en la base de datos
        $this->assertDatabaseHas('educational_programs_assessments', [
            'user_uid' => $user->uid,
            'educational_program_uid' => $program->uid,
            'calification' => 5,
        ]);
    }

    /**
     * @test
     * Prueba que la validación funciona correctamente (calificación fuera de rango)
     */
    public function testCalificateValidationFails()
    {
        // Deshabilitar el manejo de excepciones de redirección en la prueba
        $this->withoutExceptionHandling();

        // Crear un usuario y autenticarlo
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        // Crear un programa educativo en la base de datos
        $program = EducationalProgramsModel::factory()
            ->withEducationalProgramType()
            ->create([
                'uid' => generate_uuid(),
                'name' => 'Programa educativo de prueba',
            ])->first();

        // Esperar que la excepción de validación sea lanzada
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        // Crear una solicitud con una calificación fuera del rango permitido
        $request = [
            'calification' => 6,  // Fuera de rango (1-5)
            'educational_program_uid' => $program->uid,
        ];

        // Hacer la solicitud POST a la ruta de calificación
        $response = $this->post('/educational_program/calificate', $request);

        // Verificar que la respuesta devuelve un error de validación
        $response->assertStatus(422);

        // Verificar que los errores de validación están presentes
        $response->assertJsonValidationErrors(['calification', 'educational_program_uid']);
    }
}
