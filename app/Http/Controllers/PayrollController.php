<?php

namespace App\Http\Controllers;

use App\Actions\CalculatePayrollAction;
use App\Actions\RecordAuditLogAction;
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
        abort_if($payroll->period->status !== 'draft', 403, 'Payroll yang sudah final tidak dapat diubah.');

        return view('payrolls.edit', compact('payroll'));
    }

    public function update(UpdatePayrollRequest $request, Payroll $payroll, CalculatePayrollAction $calculatePayroll, RecordAuditLogAction $recordAuditLog): RedirectResponse
    {
        $oldValues = $payroll->only([...Payroll::EARNING_FIELDS, ...Payroll::DEDUCTION_FIELDS, 'notes', 'gross_income', 'total_deductions', 'net_salary']);
        $payroll->fill($request->validated())->save();
        $calculatePayroll->save($payroll);
        $payroll->refresh()->loadMissing(['employee', 'period']);
        $newValues = $payroll->only(array_keys($oldValues));
        $recordAuditLog->handle(
            'payroll.updated',
            $payroll,
            "Memperbarui payroll {$payroll->employee->full_name} periode {$payroll->period->period_date->format('Y-m')}",
            $oldValues,
            $newValues,
        );

        return redirect()->route('payrolls.show', $payroll)->with('success', 'Komponen payroll berhasil diperbarui.');
    }
}
