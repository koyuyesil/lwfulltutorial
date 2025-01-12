<?php

namespace Database\Factories;

use App\Enums\StatusType;
use App\Enums\PriorityType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = ['open', 'in_progress', 'done'];
        return [
            'user_id' => 1,
            'problem' => fake()->jobTitle(),
            'status' => fake()->randomElement(StatusType::cases()), // Get a random status from the StatusType enum
            'priority' => fake()->randomElement(PriorityType::cases()), // Get a random priority from the PriorityType enum
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
