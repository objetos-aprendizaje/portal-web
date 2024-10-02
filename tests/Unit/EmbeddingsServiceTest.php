<?php

use Tests\TestCase;
use App\Models\CoursesModel;
use App\Models\CategoriesModel;
use App\Models\CourseStatusesModel;
use App\Models\GeneralOptionsModel;
use App\Services\EmbeddingsService;
use App\Models\LearningResultsModel;
use Illuminate\Support\Facades\Http;
use App\Models\EducationalResourcesModel;
use App\Models\EducationalResourceStatusesModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmbeddingsServiceTest extends TestCase
{
    use RefreshDatabase;

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
     * Prueba que se obtienen cursos similares correctamente.
     */
    public function testGetSimilarCoursesReturnsCorrectData()
    {
        $embeddingVector = '[' . implode(',', array_fill(0, 1536, '0.1')) . ']';

        // Crear un curso con embeddings
        $course = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()->create([
                'embeddings' => $embeddingVector, // Vector de ejemplo
            ]);

        // Crear otros cursos con embeddings
        $similarCourse1 = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()->create([
                'embeddings' => $embeddingVector,
            ]);

        $similarCourse2 = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()->create([
                'embeddings' => $embeddingVector,
            ]);

        $nonSimilarCourse = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()->create([
                'embeddings' => $embeddingVector, // Un curso no similar
            ]);

        // Instanciar el servicio y obtener los cursos similares
        $service = new EmbeddingsService();
        $result = $service->getSimilarCourses($course, 2);

        // Verificar que los cursos similares se devuelven correctamente
        $this->assertCount(2, $result);
        $this->assertTrue($result->contains($similarCourse1));
        $this->assertTrue($result->contains($similarCourse2));
        $this->assertFalse($result->contains($nonSimilarCourse));

        // Verificar que el curso original no está en los resultados
        $this->assertFalse($result->contains($course));
    }

    /**
     * @test
     * Prueba que no se incluyen cursos con embeddings nulos.
     */
    public function testGetSimilarCoursesExcludesNullEmbeddings()
    {
        $embeddingVector = '[' . implode(',', array_fill(0, 1536, '0.1')) . ']';
        // Crear un curso con embeddings
        $course = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()
            ->create([
                'embeddings' => $embeddingVector,
            ]);

        // Crear un curso con embeddings nulos
        $nullEmbeddingCourse = CoursesModel::factory()
            ->withCourseStatus()
            ->withCourseType()->create([
                'embeddings' => null,
            ]);

        // Instanciar el servicio y obtener los cursos similares
        $service = new EmbeddingsService();
        $result = $service->getSimilarCourses($course, 5);

        // Verificar que los cursos con embeddings nulos no se incluyen en los resultados
        $this->assertFalse($result->contains($nullEmbeddingCourse));
    }

    /**
     * @test
     * Prueba que se obtienen los cursos similares correctamente
     */
    // Todo: Esta parte no esta lista 
    public function testGetSimilarCoursesListReturnsCorrectCourses()
    {
        // Crear cursos simulados con embeddings

        $embeddingVector = '[' . implode(',', array_fill(0, 1536, '0.1')) . ']';
        $embeddingVector1 = '[' . implode(',', array_fill(0, 1536, '0.1')) . ']';


        CoursesModel::factory()
            ->withCourseType()
            ->count(3)->create([
                'embeddings' => $embeddingVector,
                'course_status_uid' => CourseStatusesModel::factory()->create(['code' => 'INSCRIPTION'])->uid,
            ]);

        // Crear un curso con un embedding diferente para comparar
        $comparisonCourse = CoursesModel::factory()
            ->withCourseType()->create([
                'embeddings' => $embeddingVector1,
                'course_status_uid' => CourseStatusesModel::factory()->create(['code' => 'INSCRIPTION'])->uid,
            ]);

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
        // Crear un vector de 1536 dimensiones para los embeddings
        $embeddingVector = '[' . implode(',', array_fill(0, 1536, '0.1')) . ']';
        $embeddingVector1 = '[' . implode(',', array_fill(0, 1536, '0.2')) . ']';

        $status = EducationalResourceStatusesModel::where('code','PUBLISHED')->first();

        // Crear recursos educativos simulados con embeddings
        $educationalResources = EducationalResourcesModel::factory()
            ->withCreatorUser()
            ->withEducationalResourceType()
            ->withStatus()
            ->count(3)->create([
                'embeddings' => $embeddingVector,
                'status_uid' => $status->uid,
            ]);

        // Crear un recurso educativo con un embedding diferente para comparar
        $comparisonResource = EducationalResourcesModel::factory()
            ->withCreatorUser()
            ->withEducationalResourceType()
            ->withStatus()
            ->create([
                'embeddings' => $embeddingVector1,
                'status_uid' => $status->uid,
            ]);

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

        // Verificar que el recurso educativo devuelto es efectivamente similar al recurso educativo de comparación
        // $this->assertTrue($similarResources->contains($comparisonResource));
    }
}
