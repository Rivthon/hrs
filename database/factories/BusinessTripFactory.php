<?php

namespace Database\Factories;

use App\Models\BusinessTrip;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessTrip>
 */
class BusinessTripFactory extends Factory
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
            'assigned_by_user_id' => User::factory()->state(['role' => 'admin']),
            'title' => fake()->sentence(4),
            'destination' => fake()->city(),
            'purpose' => fake()->paragraph(),
            'start_date' => now()->addWeek(),
            'end_date' => now()->addWeek()->addDay(),
            'transportation' => fake()->randomElement(['Kendaraan dinas', 'Kereta', 'Pesawat']),
            'allowance' => fake()->numberBetween(0, 3000000),
            'status' => 'assigned',
        ];
    }
}
