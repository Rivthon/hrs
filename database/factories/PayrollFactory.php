<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payroll>
 */
class PayrollFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payroll_period_id' => PayrollPeriod::factory(),
            'employee_id' => Employee::factory(),
            'base_salary' => 5000000,
            'transport_allowance' => 500000,
            'gross_income' => 5500000,
            'total_deductions' => 0,
            'net_salary' => 5500000,
        ];
    }
}
