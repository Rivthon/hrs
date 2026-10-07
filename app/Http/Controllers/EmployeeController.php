<?php

namespace App\Http\Controllers;

use App\Actions\SaveEmployeeAction;
use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveType;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $employees = Employee::query()
            ->with(['department', 'position', 'user'])
            ->withSum(['leaveRequests as approved_leave_days' => fn ($query) => $query
                ->where('status', LeaveRequestStatus::Approved->value)
                ->where('leave_type', LeaveType::Annual->value)
                ->whereYear('start_date', now()->year)], 'total_working_days')
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('full_name')
            ->paginate(10)
            ->withQueryString();

        return view('employees.index', compact('employees', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('employees.create', $this->formData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request, SaveEmployeeAction $saveEmployee): RedirectResponse
    {
        $temporaryPassword = Str::password(16);
        $employee = $saveEmployee->handle($request->validated(), initialPassword: $temporaryPassword);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Data pengguna berhasil ditambahkan.')
            ->with('temporary_password', $temporaryPassword);
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee): View
    {
        $employee->load(['department', 'position', 'supervisor', 'user'])
            ->loadSum(['leaveRequests as approved_leave_days' => fn ($query) => $query
                ->where('status', LeaveRequestStatus::Approved->value)
                ->where('leave_type', LeaveType::Annual->value)
                ->whereYear('start_date', now()->year)], 'total_working_days');

        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee): View
    {
        $employee->load('user');

        return view('employees.edit', [...$this->formData($employee), 'employee' => $employee]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee, SaveEmployeeAction $saveEmployee): RedirectResponse
    {
        $saveEmployee->handle($request->validated(), $employee);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee): RedirectResponse
    {
        return back();
    }

    /**
     * @return array{departments: Collection<int, Department>, positions: Collection<int, Position>, supervisors: Collection<int, Employee>}
     */
    private function formData(?Employee $employee = null): array
    {
        return [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'positions' => Position::where('is_active', true)->orderBy('name')->get(),
            'supervisors' => Employee::query()
                ->when($employee, fn ($query) => $query->whereKeyNot($employee->id))
                ->orderBy('full_name')
                ->get(),
        ];
    }
}
