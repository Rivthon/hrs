<?php

namespace App\Actions;

use App\Models\Payroll;

class CalculatePayrollAction
{
    /**
     * @param  array<string, mixed>  $values
     * @return array{gross_income: string, total_deductions: string, net_salary: string}
     */
    public function calculate(array $values): array
    {
        $grossIncome = $this->sum($values, Payroll::EARNING_FIELDS);
        $totalDeductions = $this->sum($values, Payroll::DEDUCTION_FIELDS);

        return [
            'gross_income' => number_format($grossIncome, 2, '.', ''),
            'total_deductions' => number_format($totalDeductions, 2, '.', ''),
            'net_salary' => number_format($grossIncome - $totalDeductions, 2, '.', ''),
        ];
    }

    public function save(Payroll $payroll): Payroll
    {
        $payroll->fill($this->calculate($payroll->getAttributes()))->save();

        return $payroll->refresh();
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  array<int, string>  $fields
     */
    private function sum(array $values, array $fields): float
    {
        return collect($fields)->sum(fn (string $field): float => (float) ($values[$field] ?? 0));
    }
}
