<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'position_id' => Position::factory(),
            'employee_number' => fake()->unique()->numerify('PEG-2026-####'),
            'full_name' => fake('id_ID')->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'employment_type' => fake()->randomElement(['permanent', 'contract', 'part_time']),
            'status' => fake()->randomElement(['active', 'inactive']),
            'joined_on' => fake()->dateTimeBetween('-8 years', 'now'),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-20 years'),
            'gender' => fake()->randomElement(['male', 'female']),
            'nik' => fake()->unique()->numerify('################'),
            'nip' => fake()->unique()->numerify('##################'),
            'identity_address' => fake('id_ID')->address(),
            'residential_address' => fake('id_ID')->address(),
            'last_education' => fake()->randomElement(['SMA/SMK', 'D3', 'S1', 'S2', 'S3']),
            'university' => fake()->company(),
            'study_program' => fake()->words(2, true),
            'annual_leave_days' => 12,
            'mother_name' => fake('id_ID')->name('female'),
            'base_salary' => fake()->numberBetween(3000000, 12000000),
            'transport_allowance' => fake()->numberBetween(0, 1500000),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['status' => 'active']);
    }

    public function permanent(): static
    {
        return $this->state(fn (): array => ['employment_type' => 'permanent']);
    }
}
