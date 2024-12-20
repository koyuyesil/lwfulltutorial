<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'manufacturer' => fake()->company(),    // Örnek: "ABC Teknoloji A.Ş."
            'brand' => fake()->company(),           // Örnek: "ABC Teknoloji A.Ş."
            'model_name' => fake()->bothify('IPhone-#####'), // Örnek: "XYZ-12345"
            'model_number' => fake()->bothify('A-#####'), // Örnek: "XYZ-12345"
            'description' => fake()->sentence(),   // Örnek: "Kullanıcı dostu bir cihazdır."
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
