<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'problem' => fake()->jobTitle(),
            'priority' => fake()->colorName(),
            'status' => fake()->randomLetter(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
