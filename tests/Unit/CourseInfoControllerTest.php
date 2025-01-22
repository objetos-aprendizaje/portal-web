<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\BlocksModel;
use App\Models\CoursesModel;
use App\Models\CompetencesModel;
use App\Models\CoursesTagsModel;
use App\Models\CoursesStudentsModel;
use App\Models\LearningResultsModel;
use App\Models\CoursesAssessmentsModel;
use App\Models\CoursesPaymentTermsModel;
use App\Models\EducationalProgramTypesModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\RedirectionQueriesEducationalProgramTypesModel;

class CourseInfoControllerTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     * Prueba que el método index carga la vista correcta con los datos del curso y las competencias.
     */
    public function testIndexLoadsCourseInfoPageWithCorrectDataWithLogged()
    {
        // Crear un curso con sus relaciones

        $user = UsersModel::factory()->create();

        $this->actingAs($user);

        EducationalProgramTypesModel::factory()->create();


        $course = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create(
                [
                    'inscription_start_date' => now()->subMonths(4),
                    'inscription_finish_date' => now()->subMonths(3),
                    'enrolling_start_date' => now()->subMonths(2),
                    'enrolling_finish_date' => now()->subMonths(1),
                    'realization_start_date' =>  Carbon::now()->addDays(46)->format('Y-m-d\TH:i'),
                    'realization_finish_date' => Carbon::now()->addDays(60)->format('Y-m-d\TH:i'),
                    'cost' => 100,

                ]
            )
            ->first();

        $blocks = BlocksModel::factory()->count(3)->create(
            [
                'course_uid' => $course->uid
            ]
        );

        $learning = LearningResultsModel::factory()->withCompetence()->create()->first();

        foreach ($blocks as $block) {
            $block->learningResults()->attach($learning->uid, [
                'uid' => generate_uuid(),
            ]);
        }

        CoursesTagsModel::factory()->count(2)->create([
            'course_uid' => $course->uid,
        ]);

        $course->teachers()->attach($user->uid, [
            'uid' => generate_uuid()  // Generar un UID para la tabla pivote
        ]);

        $course->students()->attach($user->uid, [
            'uid' => generate_uuid()  // Generar un UID para la tabla pivote
        ]);

        CoursesPaymentTermsModel::factory()->create(
            [
                'uid' => generate_uuid(),
                'course_uid' => $course
            ]
        );

        CoursesAssessmentsModel::factory()->count(2)->create([
            'user_uid' => $user->uid,
            'course_uid' => $course,
        ]);

        CoursesAssessmentsModel::get();

        // Hacer la solicitud GET a la ruta del curso
        $response = $this->get("/course/" . $course->uid);

        // Verificaciones
        $response->assertStatus(200);
        $response->assertViewIs('course-info');
    }

    public function testIndexLoadsCourseInfoPageWithCorrectData()
    {
        // Crear un curso con sus relaciones
        $user = UsersModel::factory()->create();

        EducationalProgramTypesModel::factory()->create();


        $course = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create(
                [
                    'inscription_start_date' => now()->subMonths(4),
                    'inscription_finish_date' => now()->subMonths(3),
                    'enrolling_start_date' => now()->subMonths(2),
                    'enrolling_finish_date' => now()->subMonths(1),
                    'realization_start_date' =>  Carbon::now()->addDays(46)->format('Y-m-d\TH:i'),
                    'realization_finish_date' => Carbon::now()->addDays(60)->format('Y-m-d\TH:i'),
                    'cost' => 100,
                ]
            )
            ->first();

        $blocks = BlocksModel::factory()->count(3)->create(
            [
                'course_uid' => $course->uid
            ]
        );

        $learning = LearningResultsModel::factory()->withCompetence()->create()->first();

        foreach ($blocks as $block) {
            $block->learningResults()->attach($learning->uid, [
                'uid' => generate_uuid(),
            ]);
        }

        CoursesTagsModel::factory()->count(2)->create([
            'course_uid' => $course->uid,
        ]);

        $course->teachers()->attach($user->uid, [
            'uid' => generate_uuid()  // Generar un UID para la tabla pivote
        ]);

        $course->students()->attach($user->uid, [
            'uid' => generate_uuid()  // Generar un UID para la tabla pivote
        ]);

        CoursesPaymentTermsModel::factory()->create(
            [
                'uid' => generate_uuid(),
                'course_uid' => $course
            ]
        );

        CoursesAssessmentsModel::factory()->count(2)->create([
            'user_uid' => $user->uid,
            'course_uid' => $course,
        ]);

        // Hacer la solicitud GET a la ruta del curso
        $response = $this->get("/course/" . $course->uid);

        // Verificaciones
        $response->assertStatus(200);
        $response->assertViewIs('course-info');
    }

    /**
     * @test
     * Prueba la calificación de un curso.
     */
    public function testCalificateCourse()
    {
        // Crear un usuario y autenticarlo       
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        $this->actingAs($user);

        // Crear un curso
        $course = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create()->first();

        CoursesAssessmentsModel::factory()->create([
            'user_uid' => $user->uid,
            'course_uid' => $course->uid,
        ]);

        // Datos de la solicitud
        $requestData = [
            'course_uid' => $course->uid,
            'stars' => 5,
        ];

        // Hacer la solicitud POST a la ruta de calificación
        $response = $this->post('/course/calificate', $requestData);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que la calificación se ha guardado correctamente en la base de datos
        $this->assertDatabaseHas('courses_assessments', [
            'course_uid' => $course->uid,
            'user_uid' => $user->uid,
            'calification' => 5,
        ]);

        // Verificar el contenido de la respuesta JSON
        $response->assertJson([
            'success' => true,
            'message' => 'Calificación realizada con éxito',
        ]);
    }

    /**
     * @test
     * Prueba para obtener la calificación del curso
     */
    public function testGetCourseCalificationReturnsCorrectCalification()
    {
        // Crear un usuario y autenticarlo
        $user = UsersModel::where('email', 'admin@admin.com')->first();

        $this->actingAs($user);

        // Crear un curso y una calificación asociada
        $course = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create()->first();

        $calification = CoursesAssessmentsModel::factory()->create([
            'course_uid' => $course->uid,
            'user_uid' => $user->uid,
            'calification' => 5, // Calificación con varios decimales para probar el formateo
        ])->first();

        // Simular la solicitud al método
        $response = $this->post('/course/get_course_calification', [
            'course_uid' => $course->uid,
        ]);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que la calificación devuelta es correcta y formateada
        $response->assertJson([
            'calification' => number_format($calification->calification, 1),
        ]);
    }
}
