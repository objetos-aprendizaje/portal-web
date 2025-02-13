<?php

namespace Database\Factories;

use App\Models\EducationalProgramsModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EducationalProgramsDocumentsModel>
 */
class EducationalProgramsDocumentsModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uid' => generate_uuid(),
            'document_name'=> $this->faker->sentence(1),
        ];
    }
    

    public function withEducationalProgram(): static
    {
        return $this->state(fn(array $attributes) => [
            'educational_program_uid' => EducationalProgramsModel::factory()
            ->withEducationalProgramType()->create()->first(),
        ]);
    }
}
