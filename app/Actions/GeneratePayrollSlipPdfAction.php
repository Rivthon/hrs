<?php

namespace App\Actions;

use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;

class GeneratePayrollSlipPdfAction
{
    public function handle(Payroll $payroll): string
    {
        $payroll->loadMissing(['employee.position', 'employee.user', 'period']);

        return Pdf::loadView('payrolls.slip', [
            'payroll' => $payroll,
            'institution' => config('payroll.institution'),
            'signatory' => config('payroll.signatory'),
        ])->setPaper('a4')->output();
    }

    public function filename(Payroll $payroll): string
    {
        return 'slip-gaji-'.$payroll->period->period_date->format('Y-m').'-'.$payroll->employee->nip.'.pdf';
    }
}
