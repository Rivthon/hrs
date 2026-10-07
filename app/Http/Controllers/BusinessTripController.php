<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBusinessTripRequest;
use App\Models\BusinessTrip;
use App\Models\Employee;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class BusinessTripController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user->can('manage-users')) {
            $businessTrips = BusinessTrip::query()
                ->with(['employee.department', 'assignedBy'])
                ->latest('start_date')
                ->latest('id')
                ->paginate(15);
            $employees = Employee::query()
                ->with('department')
                ->where('status', 'active')
                ->orderBy('full_name')
                ->get();

            return view('business-trips.admin-index', compact('businessTrips', 'employees'));
        }

        $employee = $user->employee;
        abort_unless($employee, 404, 'Data pegawai HRS belum tersedia.');
        $businessTrips = $employee->businessTrips()
            ->with('assignedBy')
            ->latest('start_date')
            ->latest('id')
            ->paginate(15);

        return view('business-trips.employee-index', compact('businessTrips', 'employee'));
    }

    public function store(StoreBusinessTripRequest $request): RedirectResponse
    {
        BusinessTrip::create([
            ...$request->validated(),
            'allowance' => $request->validated('allowance') ?? 0,
            'assigned_by_user_id' => $request->user()->id,
            'status' => 'assigned',
        ]);

        return back()->with('success', 'Penugasan perjalanan dinas berhasil dibuat.');
    }

    public function show(BusinessTrip $businessTrip): View
    {
        $user = auth()->user();
        $isDirectSupervisor = $user->employee !== null
            && $businessTrip->employee()->where('supervisor_id', $user->employee->id)->exists();
        abort_unless($user->can('manage-users') || $businessTrip->employee_id === $user->employee?->id || $isDirectSupervisor, 403);
        $businessTrip->load(['employee.department', 'employee.position', 'assignedBy']);

        return view('business-trips.show', compact('businessTrip'));
    }
}
