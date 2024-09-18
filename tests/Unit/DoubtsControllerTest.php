<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Jobs\SendEmailJob;
use App\Models\CoursesModel;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Request;
use App\Models\EducationalProgramsModel;
use App\Models\EducationalResourcesModel;
use App\Models\EducationalProgramTypesModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\RedirectionQueriesEducationalProgramTypesModel;

class DoubtsControllerTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     * Prueba que se carga la vista 'doubts' con un tipo de objeto de aprendizaje válido
     */
    public function testIndexWithValidLearningObjectType()
    {
        // Definir un tipo de objeto de aprendizaje y un UID válido
        $learning_object_type = 'course';
        $uid = '1234-5678-91011';

        // Hacer una solicitud GET a la ruta
        $response = $this->get("/doubts/{$learning_object_type}/{$uid}");

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que se carga la vista correcta
        $response->assertViewIs('doubts');

        // Verificar que los datos correctos se pasan a la vista
        $response->assertViewHas('learning_object_type', $learning_object_type);
        $response->assertViewHas('uid', $uid);
        $response->assertViewHas('resources', ['resources/js/doubts.js']);
    }

    /**
     * @test
     * Prueba que devuelve un error 404 para un tipo de objeto de aprendizaje inválido
     */
    public function testIndexWithInvalidLearningObjectType()
    {
        // Definir un tipo de objeto de aprendizaje inválido y un UID válido
        $learning_object_type = 'invalid_type';
        $uid = '1234-5678-91011';

        // Hacer una solicitud GET a la ruta
        $response = $this->get("/doubts/{$learning_object_type}/{$uid}");

        // Verificar que la respuesta es un error 404
        $response->assertStatus(404);
    }

    /**
     * @test
     * Prueba el envío de una duda para un curso
     */
    // Todo: Me quede aqui  12-09-2024
    public function testSendDoubtForCourse()
    {
        // Simular la cola de correos
        Queue::fake();

        $educationProgramType = EducationalProgramTypesModel::factory()->create()->first();

        //Crear Factory para esto 
        RedirectionQueriesEducationalProgramTypesModel::factory()->create([
            'educational_program_type_uid' => $educationProgramType->uid,
            'type' => 'email',
            'contact' => 'Contacto'
        ]);

        $course = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create([
                'title' => 'Curso de prueba',
                'educational_program_type_uid' => $educationProgramType->uid,

            ])->latest()->first();;

        // Crear una solicitud de duda simulada
        $request = Request::create('/doubts/send_doubt', 'POST', [
            'learning_object_type' => 'course',
            'uid' => $course->uid,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'Tengo una duda sobre el curso.',
        ]);

        // Llamar al método del controlador
        $response = $this->post('/doubts/send_doubt', $request->all());

        // Verificar que el estado de la respuesta es 200
        $response->assertStatus(200);

        // Verificar que se enviaron las notificaciones
        Queue::assertPushed(SendEmailJob::class, 1);

        // Verificar que el mensaje es enviado correctamente
        // Queue::assertPushed(SendEmailJob::class, function ($job) use ($course) {
        //     return $job->parameters['learningObjectName'] === $course->title;
        // });
    }

    /**
     * @test
     * Prueba la validación de los campos incorrectos
     */
    public function testSendDoubtValidationErrors()
    {
        // Crear una solicitud de duda simulada con errores
        $request = Request::create('/doubts/send_doubt', 'POST', [
            'learning_object_type' => 'course',
            'uid' => '',
            'name' => '',
            'email' => 'invalid-email',
            'message' => '',
        ]);

        // Llamar al método del controlador
        $response = $this->post('/doubts/send_doubt', $request->all());

        // Verificar que el estado de la respuesta es 422 (Unprocessable Entity)
        $response->assertStatus(422);

        // Verificar que los errores de validación se devuelven
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
                'email',
                'message',
                'uid'
            ]
        ]);
    }

    /**
     * @test
     * Prueba el envío de una duda para un programa formativo
     */
    public function testSendDoubtForEducationalProgram()
    {
        // Simular la cola de correos
        Queue::fake();

        $educationProgramType = EducationalProgramTypesModel::factory()->create()->first();

        //Crear Factory para esto 
        RedirectionQueriesEducationalProgramTypesModel::factory()->create([
            'educational_program_type_uid' => $educationProgramType->uid,
            'type' => 'email',
            'contact' => 'email@email.com'
        ]);

        // Crear un programa formativo en la base de datos
        $program = EducationalProgramsModel::factory()
            ->create([
                'uid' => '9876-5432',
                'name' => 'Programa educativo de prueba',
                'educational_program_type_uid' => $educationProgramType->uid,
            ])->first();

        // Crear una solicitud de duda simulada
        $request = Request::create('/doubts/send_doubt', 'POST', [
            'learning_object_type' => 'educational_program',
            'uid' => $program->uid,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'message' => 'Tengo una duda sobre el programa.',
        ]);

        // Llamar al método del controlador
        $response = $this->post('/doubts/send_doubt', $request->all());

        // Verificar que el estado de la respuesta es 200
        $response->assertStatus(200);

        // Verificar que el mensaje es enviado correctamente
        Queue::assertPushed(SendEmailJob::class, 1);
        // Queue::assertPushed(SendEmailJob::class, function ($job) use ($program) {
        //     return $job->parameters['learningObjectName'] === $program->name;
        // });
    }

    /**
     * @test
     * Prueba el envío de una duda para un recurso educativo
     */
    public function testSendDoubtForEducationalResource()
    {
        // Simular la cola de correos
        Queue::fake();

        // Crear un recurso educativo en la base de datos
        $resource = EducationalResourcesModel::factory()
            ->withStatus()
            ->withEducationalResourceType()
            ->withCreatorUser()
            ->create([
                'uid' => '5678-1234',
                'title' => 'Recurso educativo de prueba',
            ])->first();

        $resource->contactEmails()->createMany([
            [
                'uid' => generate_uuid(),  // Asignar un UUID
                'email' => 'contact1@example.com',
            ],
            [
                'uid' => generate_uuid(),  // Asignar un UUID
                'email' => 'contact2@example.com',
            ],
        ]);

        // Crear una solicitud de duda simulada
        $request = Request::create('/doubts/send_doubt', 'POST', [
            'learning_object_type' => 'educational_resource',
            'uid' => $resource->uid,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'Tengo una duda sobre el recurso.',
        ]);

        // Llamar al método del controlador
        $response = $this->post('/doubts/send_doubt', $request->all());

        // Verificar que el estado de la respuesta es 200
        $response->assertStatus(200);

        // Verificar que el mensaje es enviado correctamente
        Queue::assertPushed(SendEmailJob::class, 2);
    }
}
