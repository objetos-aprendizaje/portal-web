<?php

namespace App\Services;

use App\Models\CoursesModel;
use App\Models\EducationalResourcesModel;
use App\Models\GeneralOptionsModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingsService
{
    private $openAiApiKey;

    public function __construct()
    {
        $openai_key = GeneralOptionsModel::where('option_name', 'openai_key')->first();
        $this->openAiApiKey = $openai_key ? $openai_key['option_value'] : null;
    }

    public function getEmbedding($text)
    {
        if (!$this->openAiApiKey) {
            throw new \Exception('OpenAI API key not found.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openAiApiKey,
        ])->post('https://api.openai.com/v1/embeddings', [
            'model' => 'text-embedding-3-small',
            'input' => $text,
        ]);

        return $response->json()['data'][0]['embedding'];
    }

    public function generateEmbeddingForCourse(CoursesModel $course)
    {
        $text = $course->title . ' ' . $course->description;
        $embedding = $this->getEmbedding($text);
        Log::error('Embedding generated for course ' . $course->uid . ': ' . json_encode($embedding));

        // Update the course with the new embedding
        $course->update([
            'embeddings' => $embedding,
        ]);
    }

    public function getSimilarCourses(CoursesModel $course, $limit = 5)
    {
        $embedding = $course->embeddings;

        $similarCourses = CoursesModel::select('courses.*')
            ->selectRaw('1 - (embeddings <=> ?) AS similarity', [$embedding])
            ->where('embeddings', '!=', null)
            ->where('uid', '!=', $course->uid) // Exclude the current course
            ->orderByDesc('similarity')
            ->limit($limit)
            ->get();

        return $similarCourses;
    }

    public function getSimilarCoursesList(Collection $courses, $filterCategories = [], $filterLearningResults = [], $limit = 5, $page = 1)
    {
        $uids = $courses->map(fn($course) => $course->uid)->toArray();
        $embeddings = $courses->pluck('embeddings')->map(function ($embedding) {
            // Convert the string of embeddings into an array
            return array_map('floatval', explode(',', trim($embedding, '()')));
        })->toArray();

        // Calculate the average embedding by averaging the values for each dimension
        $averageEmbedding = array_reduce($embeddings, function ($carry, $embedding) {
            foreach ($embedding as $index => $value) {
                $carry[$index] = ($carry[$index] ?? 0) + $value;
            }
            return $carry;
        }, []);

        // Divide by the number of embeddings to get the average
        $embeddingCount = count($embeddings);
        foreach ($averageEmbedding as &$value) {
            $value /= $embeddingCount;
        }

        // Convert the average embedding into a PostgreSQL vector string format
        $embeddingVectorString = '[' . implode(',', $averageEmbedding) . ']';

        $similarCoursesQuery = CoursesModel::select('courses.*')
            ->selectRaw('1 - (embeddings <=> ?) AS similarity', [$embeddingVectorString])
            ->where('embeddings', '!=', null)
            ->whereNotIn('uid', $uids)
            ->orderByDesc('similarity')
            ->with(['status', 'categories', 'blocks.learningResults'])
            ->whereHas('status', function ($query) {
                $query->where('code', 'INSCRIPTION');
            });

        if (count($filterCategories)) {
            $similarCoursesQuery->whereHas('categories', function ($query) use ($filterCategories) {
                $query->whereIn('category_uid', $filterCategories);
            });
        }

        if (count($filterLearningResults)) {
            $similarCoursesQuery->whereHas('blocks.learningResults', function ($query) use ($filterLearningResults) {
                $query->whereIn('learning_results.uid', $filterLearningResults);
            });
        }

        $similarCourses = $similarCoursesQuery->paginate($limit, ['*'], 'page', $page);

        return $similarCourses;
    }

    public function getSimilarEducationalResourcesList(Collection $educationalResources, $filterCategories = [], $filterLearningResults = [], $limit = 5, $page = 1)
    {
        $uids = $educationalResources->map(fn($educationalResource) => $educationalResource->uid)->toArray();
        $embeddings = $educationalResources->pluck('embeddings')->map(function ($embedding) {
            // Convert the string of embeddings into an array
            return array_map('floatval', explode(',', trim($embedding, '()')));
        })->toArray();

        // Calculate the average embedding by averaging the values for each dimension
        $averageEmbedding = array_reduce($embeddings, function ($carry, $embedding) {
            foreach ($embedding as $index => $value) {
                $carry[$index] = ($carry[$index] ?? 0) + $value;
            }
            return $carry;
        }, []);

        // Divide by the number of embeddings to get the average
        $embeddingCount = count($embeddings);
        foreach ($averageEmbedding as &$value) {
            $value /= $embeddingCount;
        }

        // Convert the average embedding into a PostgreSQL vector string format
        $embeddingVectorString = '[' . implode(',', $averageEmbedding) . ']';

        $similarEducationalResourcesQuery = EducationalResourcesModel::select('educational_resources.*')
            ->selectRaw('1 - (embeddings <=> ?) AS similarity', [$embeddingVectorString])
            ->where('embeddings', '!=', null)
            ->whereNotIn('uid', $uids)
            ->orderByDesc('similarity')
            ->with(['status', 'categories', 'learningResults'])
            ->whereHas('status', function ($query) {
                $query->where('code', 'PUBLISHED');
            });

        if (count($filterCategories)) {

            $similarEducationalResourcesQuery->whereHas('categories', function ($query) use ($filterCategories) {
                $query->whereIn('category_uid', $filterCategories);
            });
        }

        if (count($filterLearningResults)) {
            $similarEducationalResourcesQuery->whereHas('learningResults', function ($query) use ($filterLearningResults) {
                $query->whereIn('learning_results.uid', $filterLearningResults);
            });
        }

        $similarEducationalResources = $similarEducationalResourcesQuery->paginate($limit, ['*'], 'page', $page);

        return $similarEducationalResources;
    }

}
