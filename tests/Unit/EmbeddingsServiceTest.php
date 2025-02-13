<?php

use Tests\TestCase;
use App\Models\CoursesModel;
use App\Models\CategoriesModel;
use Illuminate\Support\Facades\DB;
use App\Models\CourseStatusesModel;
use App\Models\GeneralOptionsModel;
use App\Services\EmbeddingsService;
use App\Models\LearningResultsModel;
use Illuminate\Support\Facades\Http;
use App\Models\CoursesEmbeddingsModel;
use App\Models\EducationalResourcesModel;
use App\Models\EducationalResourceStatusesModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EducationalResourcesEmbeddingsModel;

class EmbeddingsServiceTest extends TestCase
{

    use RefreshDatabase;



    public function test_get_embedding_returns_correct_data()
    {
        // Busco openai_key y le actualizo el valor
        $general_option = GeneralOptionsModel::where('option_name', 'openai_key')->first();

        $general_option->option_value = 'test_api_key';

        $general_option->save();


        $service = new EmbeddingsService();

        Http::fake([
            'https://api.openai.com/v1/embeddings' => Http::response([
                'data' => [
                    [
                        'embedding' => [0.1, 0.2, 0.3]
                    ]
                ]
            ], 200)
        ]);

        // Act
        $result = $service->getEmbedding('test text');

        // Assert
        $this->assertEquals([0.1, 0.2, 0.3], $result);
        Http::assertSent(function ($request) {
            return $request->url() == 'https://api.openai.com/v1/embeddings' &&
                $request['model'] == 'text-embedding-3-small' &&
                $request['input'] == 'test text';
        });
    }

    /**
     * @test
     * Prueba que se lanza una excepción cuando no se encuentra la clave API.
     */
    public function testGetEmbeddingThrowsExceptionWhenApiKeyIsNotFound()
    {
        // No crear ninguna clave API en la base de datos

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('OpenAI API key not found.');

        // Instanciar el servicio y tratar de obtener el embedding
        $service = new EmbeddingsService();
        $service->getEmbedding('test text');
    }

    /**
     * @test
     * Prueba que se lanza una excepción cuando la API de OpenAI responde con un error.
     */
    public function testGetEmbeddingThrowsExceptionOnApiError()
    {
        // Simular que la clave API de OpenAI está configurada en la base de datos
        GeneralOptionsModel::factory()->create([
            'option_name' => 'openai_key',
            'option_value' => 'test-openai-api-key',
        ]);

        // Simular una respuesta de error de la API de OpenAI
        Http::fake([
            'https://api.openai.com/v1/embeddings' => Http::response(null, 500)
        ]);

        $this->expectException(\Exception::class);

        // Instanciar el servicio y tratar de obtener el embedding
        $service = new EmbeddingsService();
        $service->getEmbedding('test text');
    }


    /**
     * @test
     * Prueba que `generateEmbeddingForCourse` genere y guarde correctamente el embedding para un curso.
     */
    public function testGenerateEmbeddingForCourse()
    {

        // Busco openai_key y le actualizo el valor
        $general_option = GeneralOptionsModel::where('option_name', 'openai_key')->first();

        $general_option->option_value = 'test_api_key';

        $general_option->save();


        // Crear un curso de prueba
        $course = CoursesModel::factory()->withCourseStatus()->withCourseType()->create([
            'title' => 'Curso de Prueba',
            'description' => 'Descripción del curso de prueba.'
        ]);

        // Mock del servicio y simulación del método `getEmbedding`
        $mockService = Mockery::mock(EmbeddingsService::class)->makePartial();
        $mockService->shouldReceive('getEmbedding')
            ->andReturn(array_fill(0, 150, 0.1));

        // Simulación de la respuesta de OpenAI
        Http::fake([
            'https://api.openai.com/v1/embeddings' => Http::response([
                'data' => [
                    [
                        'embedding' => array_fill(0, 150, 0.1)
                    ]
                ]
            ], 200)
        ]);

        // Llamar al método
        $mockService->generateEmbeddingForCourse($course);

        // Verificar que el embedding se ha guardado en la base de datos
        $this->assertDatabaseHas('courses_embeddings', [
            'course_uid' => $course->uid,
        ]);

        // Validar el contenido del embedding en la base de datos
        $savedEmbedding = CoursesEmbeddingsModel::where('course_uid', $course->uid)->first();
        $this->assertNotNull($savedEmbedding);
        $this->assertIsArray($savedEmbedding->embeddings);
        $this->assertCount(150, $savedEmbedding->embeddings);
    }

    /**
     * @test
     * Prueba que se obtienen los cursos similares correctamente
     */

    public function testGetSimilarCoursesListReturnsCorrectCourses()
    {
        // Crear cursos simulados con embeddings
        $courses = CoursesModel::factory()->count(3)
            ->withCourseType()
            ->create([
                // 'embeddings' => $embeddingVector,
                'course_status_uid' => CourseStatusesModel::factory()->create(['code' => 'INSCRIPTION'])->uid,
            ]);

        foreach ($courses as $course) {

            CoursesEmbeddingsModel::factory()->create(
                [
                    'course_uid' => $course->uid,
                ]
            );
        }

        // Crear un curso con un embedding diferente para comparar
        $comparisonCourse = CoursesModel::factory()
            ->withCourseType()->create([
                // 'embeddings' => $embeddingVector1,
                'course_status_uid' => CourseStatusesModel::factory()->create(['code' => 'INSCRIPTION'])->uid,
            ]);

        CoursesEmbeddingsModel::factory()->create(
            [
                'course_uid' => $comparisonCourse->uid,
            ]
        );

        // Simular las categorías y resultados de aprendizaje
        $category = CategoriesModel::factory()->create()->first();
        $learningResult = LearningResultsModel::factory()->withCompetence()->create()->first();

        // Asignar categoría al curso para aplicar el filtro
        $comparisonCourse->categories()->attach(
            $category->uid,
            [
                'uid' => generate_uuid(),
            ]
        );

        $courses = CoursesModel::get();

        // Convertir el curso de comparación en una colección Eloquent
        $coursesCollection = $courses->push($comparisonCourse);

        // Instanciar el servicio
        $service = new EmbeddingsService();

        // Obtener cursos similares con filtros aplicados
        $similarCourses = $service->getSimilarCoursesList(
            $coursesCollection, // Pasar la colección Eloquent
            [$category->uid], // Filtro de categoría
            [$learningResult->uid], // Filtro de resultados de aprendizaje
            5, // Límite de resultados
            1 // Página actual
        );


        $this->assertEquals(0, $similarCourses->count());
        
    }

    /**
     * @test
     * Prueba que el método getSimilarEducationalResourcesList devuelve los recursos educativos correctos.
     */
    public function testGetSimilarEducationalResourcesListReturnsCorrectResources()
    {

        $status = EducationalResourceStatusesModel::where('code', 'PUBLISHED')->first();

        // Crear recursos educativos simulados con embeddings
        $educationalResources = EducationalResourcesModel::factory()
            ->withCreatorUser()
            ->withEducationalResourceType()
            ->withStatus()
            ->count(3)->create([
                'status_uid' => $status->uid,
            ]);

        foreach ($educationalResources as $educationalResource) {

            EducationalResourcesEmbeddingsModel::factory()->create(
                [
                    'educational_resource_uid' => $educationalResource->uid
                ]
            );
        }


        // Crear un recurso educativo con un embedding diferente para comparar
        $comparisonResource = EducationalResourcesModel::factory()
            ->withCreatorUser()
            ->withEducationalResourceType()
            ->withStatus()
            ->create([
                'status_uid' => $status->uid,
            ]);

        EducationalResourcesEmbeddingsModel::factory()->create(
            [
                'educational_resource_uid' =>  $comparisonResource->uid
            ]
        );

        // Simular las categorías y resultados de aprendizaje
        $category = CategoriesModel::factory()->create()->first();
        $learningResult = LearningResultsModel::factory()->withCompetence()->create()->first();

        // Asignar categoría al recurso educativo para aplicar el filtro
        $comparisonResource->categories()->attach(
            $category->uid,
            [
                'uid' => generate_uuid(),
            ]
        );

        // Convertir el recurso educativo de comparación en una colección Eloquent
        $resourcesCollection = $educationalResources->push($comparisonResource);

        // Instanciar el servicio
        $service = new EmbeddingsService();

        // Obtener recursos educativos similares con filtros aplicados
        $similarResources = $service->getSimilarEducationalResourcesList(
            $resourcesCollection, // Pasar la colección Eloquent
            [$category->uid], // Filtro de categoría
            [$learningResult->uid], // Filtro de resultados de aprendizaje
            5, // Límite de resultados
            1 // Página actual
        );

        // Verificar que se devuelve la cantidad correcta de recursos educativos
        $this->assertEquals(0, $similarResources->count());

        
    }
}
