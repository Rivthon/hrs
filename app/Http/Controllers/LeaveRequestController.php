<?php

namespace App\Http\Controllers;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveType;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', LeaveRequest::class);
        $employee = auth()->user()->employee;
        $employee->loadSum(['leaveRequests as approved_leave_days' => fn ($query) => $query
            ->where('status', LeaveRequestStatus::Approved->value)
            ->where('leave_type', LeaveType::Annual->value)
            ->whereYear('start_date', now()->year)], 'total_working_days');
        $myRequests = LeaveRequest::query()->with(['replacement', 'directSupervisor'])
            ->whereBelongsTo($employee)->latest('submitted_at')->paginate(10);
        $supervisorRequests = LeaveRequest::query()->with(['employee', 'replacement'])
            ->whereBelongsTo($employee, 'directSupervisor')
            ->where('status', LeaveRequestStatus::PendingSupervisor)->latest('submitted_at')->get();
        $hrRequests = collect();

        if (in_array(auth()->user()->role, ['admin', 'hr'], true)) {
            $hrRequests = LeaveRequest::query()->with(['employee', 'replacement', 'supervisorApprover'])
                ->where('status', LeaveRequestStatus::PendingHr)->latest('submitted_at')->get();
        }

        return view('leave-requests.index', compact('employee', 'myRequests', 'supervisorRequests', 'hrRequests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', LeaveRequest::class);
        $employee = auth()->user()->employee;
        $replacements = Employee::query()->with('department')->whereKeyNot($employee->id)->where('status', 'active')->orderBy('full_name')->get();

        return view('leave-requests.create', compact('employee', 'replacements'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        $documentPath = $request->hasFile('supporting_document')
            ? $request->file('supporting_document')->store('leave-documents')
            : null;
        $leaveRequest = LeaveRequest::create([
            ...$request->safe()->only(['leave_type', 'replacement_employee_id', 'start_date', 'end_date', 'start_time', 'end_time', 'reason']),
            'employee_id' => $employee->id,
            'direct_supervisor_id' => $employee->supervisor_id,
            'total_working_days' => $request->workingDays(),
            'duration_minutes' => $request->durationMinutes(),
            'supporting_document_path' => $documentPath,
            'status' => LeaveRequestStatus::PendingSupervisor,
            'submitted_at' => now(),
        ]);

        return redirect()->route('leave-requests.show', $leaveRequest)
            ->with('success', 'Pengajuan cuti dikirim kepada atasan langsung.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveRequest $leaveRequest): View
    {
        Gate::authorize('view', $leaveRequest);
        $leaveRequest->load(['employee', 'replacement', 'directSupervisor', 'supervisorApprover', 'hrApprover']);

        return view('leave-requests.show', compact('leaveRequest'));
    }
}
