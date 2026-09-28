<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeExportController;
use App\Http\Controllers\EmployeeImportController;
use App\Http\Controllers\EmployeeImportTemplateController;
use App\Http\Controllers\HrLeaveApprovalController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PayrollPeriodController;
use App\Http\Controllers\PayrollPeriodSlipEmailController;
use App\Http\Controllers\PayrollSlipController;
use App\Http\Controllers\PayrollSlipEmailController;
use App\Http\Controllers\PublicHolidayController;
use App\Http\Controllers\SupervisorLeaveApprovalController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');
Route::middleware('guest')->group(function (): void {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1');
});
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::resource('leave-requests', LeaveRequestController::class)->only(['index', 'create', 'store', 'show']);
    Route::put('leave-requests/{leaveRequest}/supervisor-approval', [SupervisorLeaveApprovalController::class, 'update'])->name('leave-requests.supervisor-approval');
    Route::put('leave-requests/{leaveRequest}/hr-approval', [HrLeaveApprovalController::class, 'update'])->name('leave-requests.hr-approval');
});

Route::middleware(['auth', 'can:manage-users'])->group(function (): void {
    Route::get('employees/export', EmployeeExportController::class)->name('employees.export');
    Route::get('employees/import-template', EmployeeImportTemplateController::class)->name('employees.import-template');
    Route::post('employees/import', [EmployeeImportController::class, 'store'])->name('employees.import');
    Route::resource('employees', EmployeeController::class)->except('destroy');
    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::resource('public-holidays', PublicHolidayController::class)->only(['index', 'store', 'destroy']);
    Route::resource('payroll-periods', PayrollPeriodController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::post('payroll-periods/{payrollPeriod}/email-slips', [PayrollPeriodSlipEmailController::class, 'store'])->name('payroll-periods.email-slips.store');
    Route::get('payrolls/{payroll}', [PayrollController::class, 'show'])->name('payrolls.show');
    Route::get('payrolls/{payroll}/edit', [PayrollController::class, 'edit'])->name('payrolls.edit');
    Route::put('payrolls/{payroll}', [PayrollController::class, 'update'])->name('payrolls.update');
    Route::get('payrolls/{payroll}/slip', PayrollSlipController::class)->name('payrolls.slip');
    Route::post('payrolls/{payroll}/email-slip', [PayrollSlipEmailController::class, 'store'])->name('payrolls.email-slip.store');
});
