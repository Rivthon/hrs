<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeTodo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeTodo>
 */
class EmployeeTodoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'title' => fake()->sentence(5),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+1 month'),
            'priority' => fake()->randomElement(['low', 'normal', 'high']),
            'is_completed' => false,
        ];
    }
}
