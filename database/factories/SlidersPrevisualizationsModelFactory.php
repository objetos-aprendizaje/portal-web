<?php

namespace Database\Factories;

use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SlidersPrevisualizationsModel>
 */
class SlidersPrevisualizationsModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $demoImages = collect(File::files(base_path('public/images/test-images')))
            ->map(function ($file) {
                return str_replace(base_path('public/'), '', $file->getPathname());
            })->toArray();
            
        return [
            'uid' => generate_uuid(),
            'title' => $this->faker->word(3),
            'description' => substr($this->faker->paragraph, 0, 255),
            'image_path' =>$demoImages[array_rand($demoImages)] ,
            'color' => '#bcedef',
        ];
    }
}
