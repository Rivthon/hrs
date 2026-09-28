<?php

namespace App\Http\Controllers;

use App\Jobs\SendPayrollSlipEmail;
use App\Models\Payroll;
use Illuminate\Http\RedirectResponse;

class PayrollSlipEmailController extends Controller
{
    public function store(Payroll $payroll): RedirectResponse
    {
        if (blank($payroll->employee->email)) {
            return back()->with('error', 'Slip tidak dikirim karena email aktif karyawan belum tersedia.');
        }

        SendPayrollSlipEmail::dispatch($payroll);

        return back()->with('success', 'Slip gaji dijadwalkan untuk dikirim ke '.$payroll->employee->email.'.');
    }
}
