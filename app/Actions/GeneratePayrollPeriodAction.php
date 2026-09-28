<?php

namespace App\Actions;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\DB;

class GeneratePayrollPeriodAction
{
    public function __construct(private CalculatePayrollAction $calculatePayroll) {}

    public function handle(PayrollPeriod $period): PayrollPeriod
    {
        return DB::transaction(function () use ($period): PayrollPeriod {
            Employee::query()
                ->where('status', 'active')
                ->chunkById(200, function ($employees) use ($period): void {
                    foreach ($employees as $employee) {
                        $payroll = Payroll::firstOrCreate(
                            ['payroll_period_id' => $period->id, 'employee_id' => $employee->id],
                            ['base_salary' => $employee->base_salary, 'transport_allowance' => $employee->transport_allowance],
                        );

                        $this->calculatePayroll->save($payroll);
                    }
                });

            $period->update(['generated_at' => now()]);

            return $period->refresh();
        });
    }
}
