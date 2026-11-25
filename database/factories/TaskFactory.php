<?php

namespace Database\Factories;

use App\Enums\PriorityType;
use App\Enums\Recurrence;
use App\Enums\StatusType;
use App\Enums\TaskCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        $category = fake()->randomElement(TaskCategory::cases());

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(),
            'category' => $category,
            'due_at' => fake()->dateTimeBetween('+1 day', '+1 month'),
            'status' => StatusType::OPEN,
            'priority' => fake()->randomElement(PriorityType::cases()),
            'recurrence' => Recurrence::NONE,
            'amount' => $category->hasAmount() ? fake()->randomFloat(2, 100, 5000) : null,
            'user_id' => \App\Models\User::factory(),
        ];
    }

    public function recurring(Recurrence $recurrence, int $interval = 1): static
    {
        return $this->state(['recurrence' => $recurrence, 'recurrence_interval' => $interval]);
    }
}
