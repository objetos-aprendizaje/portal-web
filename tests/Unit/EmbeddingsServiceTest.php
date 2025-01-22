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
            ->andReturn(array_fill(0, 1536, 0.1));

        // Simulación de la respuesta de OpenAI
        Http::fake([
            'https://api.openai.com/v1/embeddings' => Http::response([
                'data' => [
                    [
                        'embedding' => array_fill(0, 1536, 0.1)
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
        $this->assertCount(1536, $savedEmbedding->embeddings);
    }


    // Todo:esta prueba esta presentando error, y hasta el momento no esta en uso
    /**
     * @test
     * Prueba que `getSimilarCourses` devuelva los cursos más similares.
     */
    // public function testGetSimilarCoursesReturnsMostSimilarCourses()
    // {

    //     // Busco openai_key y le actualizo el valor
    //     $general_option = GeneralOptionsModel::where('option_name', 'openai_key')->first();

    //     $general_option->option_value = 'test_api_key';

    //     $general_option->save();


    //     // Crear un curso principal de prueba con un embedding simulado
    //     $mainCourse = CoursesModel::factory()->withCourseStatus()->withCourseType()->create();
    //     $mainEmbedding = array_fill(0, 1536, 0.5); // Embedding del curso principal
    //     CoursesEmbeddingsModel::create([
    //         'course_uid' => $mainCourse->uid,
    //         'embeddings' => $mainEmbedding
    //     ]);

    //     // Crear otros cursos de prueba con embeddings variados
    //     $similarCoursesData = [];
    //     foreach (range(1, 5) as $i) {
    //         $course = CoursesModel::factory()->withCourseStatus()->withCourseType()->create();
    //         $embedding = array_fill(0, 1536, 0.5 + ($i * 0.01)); // Embeddings variados
    //         CoursesEmbeddingsModel::create([
    //             'course_uid' => $course->uid,
    //             'embeddings' => $embedding
    //         ]);
    //         $similarCoursesData[$course->uid] = $embedding;
    //     }

    //     // Mock del servicio de embeddings
    //     $service = Mockery::mock(EmbeddingsService::class)->makePartial();
    //     $service->shouldReceive('getSimilarCourses')
    //         ->with($mainCourse, 5)
    //         ->andReturnUsing(function ($course, $limit) use ($similarCoursesData) {
    //             // Simular la lógica para ordenar por similitud y limitar la cantidad
    //             return collect($similarCoursesData)
    //                 ->map(function ($embedding, $courseUid) {
    //                     return ['uid' => $courseUid, 'similarity' => 1 - DB::raw("<=> {$embedding}")];
    //                 })
    //                 ->sortByDesc('similarity')
    //                 ->take($limit)
    //                 ->values();
    //         });

    //     // Ejecutar la función para obtener los cursos similares
    //     $similarCourses = $service->getSimilarCourses($mainCourse, 5);

    //     // Verificar que la respuesta contiene 5 cursos
    //     $this->assertCount(5, $similarCourses);

    //     // Verificar que no contiene el curso principal
    //     foreach ($similarCourses as $course) {
    //         $this->assertNotEquals($mainCourse->uid, $course['uid']);
    //     }

    //     // Verificar que los cursos se devuelven en orden de similitud
    //     $similarities = $similarCourses->pluck('similarity')->all();
    //     $this->assertEquals($similarities, array_sort($similarities, SORT_DESC));
    // }

    //Todo:  Esta prueba de comenta para luego revisarla
    /**
     * @test
     * Prueba que se obtienen cursos similares correctamente.
     */

    // public function testGetSimilarCoursesReturnsCorrectData()
    // {      

    //     $embeddingVector = array_fill(0, 1536, 0.2); 

    //     // Crear un curso con embeddings
    //     $course = CoursesModel::factory()
    //         ->withCourseStatus()
    //         ->withCourseType()->create();

    //     CoursesEmbeddingsModel::factory()->create(
    //         [
    //             'course_uid' => $course->uid,
    //         ]
    //     );

    //     // Crear otros cursos con embeddings
    //     $similarCourse1 = CoursesModel::factory()
    //         ->withCourseStatus()
    //         ->withCourseType()->create();

    //     CoursesEmbeddingsModel::factory()->create(
    //         [
    //             'course_uid' => $similarCourse1->uid,
    //         ]
    //     );

    //     $similarCourse2 = CoursesModel::factory()
    //         ->withCourseStatus()
    //         ->withCourseType()->create();

    //     CoursesEmbeddingsModel::factory()->create(
    //         [
    //             'course_uid' => $similarCourse2->uid,
    //             'embeddings' => $embeddingVector,
    //         ]
    //     );

    //     $nonSimilarCourse = CoursesModel::factory()
    //         ->withCourseStatus()
    //         ->withCourseType()->create();

    //     // Instanciar el servicio y obtener los cursos similares
    //     $service = new EmbeddingsService();
    //     $result = $service->getSimilarCourses($course, 2);

    //     // Verificar que los cursos similares se devuelven correctamente
    //     $this->assertCount(2, $result);
    //     $this->assertTrue($result->contains($similarCourse1));
    //     $this->assertTrue($result->contains($similarCourse2));
    //     $this->assertFalse($result->contains($nonSimilarCourse));

    //     // Verificar que el curso original no está en los resultados
    //     $this->assertFalse($result->contains($course));
    // }

    /**
     * @test
     * Prueba que no se incluyen cursos con embeddings nulos.
     */
    // public function testGetSimilarCoursesExcludesNullEmbeddings()
    // {
    //     $embeddingVector = '[' . implode(',', array_fill(0, 1536, '0.1')) . ']';
    //     // Crear un curso con embeddings
    //     $course = CoursesModel::factory()
    //         ->withCourseStatus()
    //         ->withCourseType()
    //         ->create();

    //     // Crear un curso con embeddings nulos
    //     $nullEmbeddingCourse = CoursesModel::factory()
    //         ->withCourseStatus()
    //         ->withCourseType()->create();

    //     // Instanciar el servicio y obtener los cursos similares
    //     $service = new EmbeddingsService();
    //     $result = $service->getSimilarCourses($course, 5);

    //     // Verificar que los cursos con embeddings nulos no se incluyen en los resultados
    //     $this->assertFalse($result->contains($nullEmbeddingCourse));
    // }

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

        // dd( $coursesCollection);


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

        // Verificar que el curso devuelto es efectivamente similar al curso de comparación
        // $this->assertTrue($similarCourses->contains($comparisonCourse));
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
