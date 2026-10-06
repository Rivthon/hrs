<?php

namespace App\Actions;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\DB;

class GeneratePayrollPeriodAction
{
    public function __construct(
        private CalculatePayrollAction $calculatePayroll,
        private CalculatePasTeachingHonorAction $calculatePasTeachingHonor,
    ) {}

    public function handle(PayrollPeriod $period): PayrollPeriod
    {
        return DB::transaction(function () use ($period): PayrollPeriod {
            Employee::query()
                ->where('status', 'active')
                ->with('user:id,role')
                ->chunkById(200, function ($employees) use ($period): void {
                    $pasLecturerIds = $employees
                        ->filter(fn (Employee $employee): bool => $employee->user?->role === 'dosen' && filled($employee->pas_dosen_id))
                        ->pluck('pas_dosen_id')
                        ->map(fn ($id): int => (int) $id)
                        ->values()
                        ->all();
                    $teachingHonors = $this->calculatePasTeachingHonor->handle($pasLecturerIds, $period->period_date);

                    foreach ($employees as $employee) {
                        $payroll = Payroll::firstOrCreate(
                            ['payroll_period_id' => $period->id, 'employee_id' => $employee->id],
                            [
                                'base_salary' => $employee->base_salary,
                                'transport_allowance' => $employee->transport_allowance,
                                'teaching_honor' => $teachingHonors[(int) $employee->pas_dosen_id] ?? 0,
                            ],
                        );

                        $this->calculatePayroll->save($payroll);
                    }
                });

            $period->update(['generated_at' => now()]);

            return $period->refresh();
        });
    }
}
