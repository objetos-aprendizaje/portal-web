<?php

namespace Database\Factories;

use App\Models\UsersModel;
use App\Models\EducationalProgramsModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EducationalProgramsAssessmentsModel>
 */
class EducationalProgramsAssessmentsModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uid' =>  generate_uuid(),
            'calification' => $this->faker->randomNumber(2)
        ];
    }

    public function withEducationalProgram(): static
    {
        return $this->state(fn() => [
            'course_status_uid' => EducationalProgramsModel::factory()
                ->withEducationalProgramType()
                ->create()->first(),
        ]);
    }

    public function withUser(): Factory
    {
        return $this->state(function () {
            return [
                'user_uid' => UsersModel::factory()->create()->first(),
            ];
        });
    }
}
