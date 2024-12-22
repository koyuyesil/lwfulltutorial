<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Enums\PriorityType;
use App\Enums\StatusType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(), // Random sentence for title
            'slug' => fake()->slug(), // Generate a proper slug
            'description' => fake()->paragraph(), // Random paragraph for description
            'deadline'  => fake()->dateTimeBetween('+1 day', '+1 month'), // A random date for the deadline
            'status' => fake()->randomElement(StatusType::cases()), // Get a random status from the StatusType enum
            'priority' => fake()->randomElement(PriorityType::cases()), // Get a random priority from the PriorityType enum
            'created_at' => now(),
            'updated_at' => now(),
            'user_id' => 1, // Assuming user_id is fixed as 1//bu araa bu eziliyor
        ];
    }
}
