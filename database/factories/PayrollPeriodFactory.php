<?php

namespace Database\Factories;

use App\Models\PayrollPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollPeriod>
 */
class PayrollPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'period_date' => fake()->unique()->dateTimeBetween('-1 year', '+1 year')->format('Y-m-01'),
            'status' => 'draft',
            'generated_at' => now(),
        ];
    }
}
