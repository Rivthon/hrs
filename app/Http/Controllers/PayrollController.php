<?php

namespace App\Http\Controllers;

use App\Actions\CalculatePayrollAction;
use App\Http\Requests\UpdatePayrollRequest;
use App\Models\Payroll;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PayrollController extends Controller
{
    public function show(Payroll $payroll): View
    {
        $payroll->load(['employee.department', 'employee.position', 'employee.user', 'period']);

        return view('payrolls.show', compact('payroll'));
    }

    public function edit(Payroll $payroll): View
    {
        $payroll->load(['employee.user', 'period']);

        return view('payrolls.edit', compact('payroll'));
    }

    public function update(UpdatePayrollRequest $request, Payroll $payroll, CalculatePayrollAction $calculatePayroll): RedirectResponse
    {
        $payroll->fill($request->validated())->save();
        $calculatePayroll->save($payroll);

        return redirect()->route('payrolls.show', $payroll)->with('success', 'Komponen payroll berhasil diperbarui.');
    }
}
