<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\WorkReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkReport>
 */
class WorkReportFactory extends Factory
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
            'report_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['progress', 'completed']),
        ];
    }
}
