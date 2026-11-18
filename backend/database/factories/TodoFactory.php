<?php

namespace Database\Factories;

use App\Enums\TodoPriority;
use App\Enums\TodoStatus;
use App\Models\Todo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Todo>
 */
class TodoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'assignee' => fake()->optional(0.8)->firstName(),
            'due_date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'time_tracked' => fake()->randomFloat(2, 0, 16),
            'status' => fake()->randomElement(TodoStatus::cases()),
            'priority' => fake()->randomElement(TodoPriority::cases()),
        ];
    }
}