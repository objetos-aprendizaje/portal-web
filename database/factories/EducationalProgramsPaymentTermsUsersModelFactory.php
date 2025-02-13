<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EducationalProgramsPaymentTermsUsersModel>
 */
class EducationalProgramsPaymentTermsUsersModelFactory extends Factory
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
            'order_number' =>$this->faker->numberBetween(1, 30),
            'is_paid' => $this->faker->numberBetween(0, 1),
        ];
    }
}
