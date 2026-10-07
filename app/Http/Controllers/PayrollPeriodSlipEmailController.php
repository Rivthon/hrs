<?php

namespace App\Http\Controllers;

use App\Actions\RecordAuditLogAction;
use App\Jobs\SendPayrollSlipEmail;
use App\Models\PayrollPeriod;
use Illuminate\Http\RedirectResponse;

class PayrollPeriodSlipEmailController extends Controller
{
    public function store(PayrollPeriod $payrollPeriod, RecordAuditLogAction $recordAuditLog): RedirectResponse
    {
        $queued = 0;

        $payrollPeriod->payrolls()
            ->whereHas('employee', fn ($query) => $query->whereNotNull('email')->where('email', '!=', ''))
            ->orderBy('id')
            ->chunkById(100, function ($payrolls) use (&$queued): void {
                foreach ($payrolls as $payroll) {
                    SendPayrollSlipEmail::dispatch($payroll);
                    $queued++;
                }
            });

        if ($queued === 0) {
            return back()->with('error', 'Tidak ada karyawan dengan email aktif pada periode ini.');
        }

        if ($payrollPeriod->status === 'draft') {
            $payrollPeriod->update([
                'status' => 'finalized',
                'finalized_at' => now(),
            ]);
            $recordAuditLog->handle(
                'payroll.finalized',
                $payrollPeriod,
                'Memfinalkan payroll periode '.$payrollPeriod->period_date->format('Y-m'),
                ['status' => 'draft', 'finalized_at' => null],
                ['status' => 'finalized', 'finalized_at' => $payrollPeriod->finalized_at],
            );
        }

        return back()->with('success', "Payroll berhasil difinalkan. {$queued} slip gaji masuk antrean pengiriman email.");
    }
}
