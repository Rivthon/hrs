<?php

namespace App\Http\Controllers;

use App\Actions\GeneratePayrollPeriodAction;
use App\Http\Requests\StorePayrollPeriodRequest;
use App\Models\PayrollPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PayrollPeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $periods = PayrollPeriod::query()
            ->withCount('payrolls')
            ->withSum('payrolls', 'net_salary')
            ->latest('period_date')
            ->paginate(12);

        return view('payroll-periods.index', compact('periods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(StorePayrollPeriodRequest $request, GeneratePayrollPeriodAction $generatePayroll): RedirectResponse
    {
        $period = PayrollPeriod::create([
            'period_date' => $request->validated('period_date'),
            'status' => 'draft',
        ]);
        $generatePayroll->handle($period);

        return redirect()->route('payroll-periods.show', $period)
            ->with('success', 'Periode payroll dibuat dan data pegawai aktif berhasil dimuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, PayrollPeriod $payrollPeriod): View
    {
        $search = $request->string('search')->trim()->toString();
        $payrolls = $payrollPeriod->payrolls()
            ->with(['employee.department', 'employee.user'])
            ->when($search, fn ($query) => $query->whereHas('employee', function ($query) use ($search): void {
                $query->where('full_name', 'like', "%{$search}%")->orWhere('nip', 'like', "%{$search}%");
            }))
            ->orderBy('employee_id')
            ->paginate(15)
            ->withQueryString();
        $summary = [
            'gross_income' => $payrollPeriod->payrolls()->sum('gross_income'),
            'total_deductions' => $payrollPeriod->payrolls()->sum('total_deductions'),
            'net_salary' => $payrollPeriod->payrolls()->sum('net_salary'),
        ];

        return view('payroll-periods.show', compact('payrollPeriod', 'payrolls', 'search', 'summary'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PayrollPeriod $payrollPeriod): RedirectResponse
    {
        $periodName = $payrollPeriod->period_date->locale('id')->translatedFormat('F Y');

        $payrollPeriod->delete();

        return redirect()->route('payroll-periods.index')
            ->with('success', "Periode payroll {$periodName} beserta seluruh rinciannya berhasil dihapus.");
    }
}
