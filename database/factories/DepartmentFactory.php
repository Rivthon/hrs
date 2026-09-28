<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('UNIT-###'),
            'name' => fake()->unique()->randomElement([
                'Fakultas Teknologi Informasi',
                'Fakultas Ekonomi dan Bisnis',
                'Biro Akademik',
                'Biro Sumber Daya Manusia',
                'Unit Perpustakaan',
            ]),
            'type' => fake()->randomElement(['faculty', 'administration', 'support']),
            'is_active' => true,
        ];
    }
}
