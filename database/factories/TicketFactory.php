<?php

namespace Database\Factories;

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
            'priority' => fake()->colorName(),
            'status' => $status[array_rand($status)],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
