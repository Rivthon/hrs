<?php

namespace Database\Factories;

use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Dosen',
                'Kepala Program Studi',
                'Staf Administrasi',
                'Analis Sistem',
                'Pustakawan',
            ]),
            'category' => fake()->randomElement(['academic', 'structural', 'support']),
            'level' => fake()->optional()->numberBetween(1, 5),
            'is_active' => true,
        ];
    }
}
