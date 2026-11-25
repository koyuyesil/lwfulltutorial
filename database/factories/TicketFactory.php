<?php

namespace Database\Factories;

use App\Enums\TicketStatus;
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
        return [
            'user_id' => \App\Models\User::factory(),
            'client_product_id' => \App\Models\ClientProduct::factory(),
            'problem' => fake()->jobTitle(),
            'status' => fake()->randomElement(TicketStatus::cases()),
            'priority' => fake()->randomElement(PriorityType::cases()), // Get a random priority from the PriorityType enum
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (\App\Models\Ticket $ticket) {
            if ($ticket->status?->isClosed() && ! $ticket->closed_at) {
                $ticket->closed_at = now();
            }
        });
    }

    public function status(TicketStatus $status): static
    {
        return $this->state(['status' => $status]);
    }
}
