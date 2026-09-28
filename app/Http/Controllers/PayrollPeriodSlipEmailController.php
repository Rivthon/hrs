<?php

namespace App\Http\Controllers;

use App\Jobs\SendPayrollSlipEmail;
use App\Models\PayrollPeriod;
use Illuminate\Http\RedirectResponse;

class PayrollPeriodSlipEmailController extends Controller
{
    public function store(PayrollPeriod $payrollPeriod): RedirectResponse
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

        return back()->with('success', "{$queued} slip gaji dijadwalkan untuk dikirim melalui email.");
    }
}
