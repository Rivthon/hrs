<?php

namespace App\Http\Controllers;

use App\Actions\ProcessLeaveApprovalAction;
use App\Http\Requests\LeaveApprovalDecisionRequest;
use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class SupervisorLeaveApprovalController extends Controller
{
    public function update(LeaveApprovalDecisionRequest $request, LeaveRequest $leaveRequest, ProcessLeaveApprovalAction $processApproval): RedirectResponse
    {
        Gate::authorize('supervisorApprove', $leaveRequest);
        $processApproval->bySupervisor($leaveRequest, $request->user()->employee, $request->string('decision')->toString(), $request->string('notes')->toString() ?: null);

        return redirect()->route('leave-requests.index')->with('success', 'Keputusan atasan berhasil disimpan.');
    }
}
