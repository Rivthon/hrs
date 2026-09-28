<?php

namespace App\Http\Controllers;

use App\Actions\ProcessLeaveApprovalAction;
use App\Http\Requests\LeaveApprovalDecisionRequest;
use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class HrLeaveApprovalController extends Controller
{
    public function update(LeaveApprovalDecisionRequest $request, LeaveRequest $leaveRequest, ProcessLeaveApprovalAction $processApproval): RedirectResponse
    {
        Gate::authorize('hrApprove', $leaveRequest);
        $processApproval->byHr($leaveRequest, $request->user()->employee, $request->string('decision')->toString(), $request->string('notes')->toString() ?: null);

        return redirect()->route('leave-requests.index')->with('success', 'Keputusan SDM berhasil disimpan.');
    }
}
