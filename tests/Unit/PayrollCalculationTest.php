<?php

namespace Tests\Unit;

use App\Actions\CalculatePayrollAction;
use PHPUnit\Framework\TestCase;

class PayrollCalculationTest extends TestCase
{
    public function test_calculates_gross_deductions_and_net_salary(): void
    {
        $result = (new CalculatePayrollAction)->calculate([
            'base_salary' => 5000000,
            'transport_allowance' => 500000,
            'position_allowance' => 1000000,
            'functional_allowance' => 500000,
            'bpjs_employment_deduction' => 100000,
            'bpjs_health_deduction' => 100000,
            'income_tax_deduction' => 100000,
        ]);

        $this->assertSame('7000000.00', $result['gross_income']);
        $this->assertSame('300000.00', $result['total_deductions']);
        $this->assertSame('6700000.00', $result['net_salary']);
    }
}
