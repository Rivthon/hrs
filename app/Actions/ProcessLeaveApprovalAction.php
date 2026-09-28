<?php

namespace App\Actions;

use App\Enums\LeaveRequestStatus;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\DB;
use LogicException;

class ProcessLeaveApprovalAction
{
    public function bySupervisor(LeaveRequest $leaveRequest, Employee $approver, string $decision, ?string $notes): LeaveRequest
    {
        return $this->process($leaveRequest, function (LeaveRequest $locked) use ($approver, $decision, $notes): void {
            if ($locked->status !== LeaveRequestStatus::PendingSupervisor) {
                throw new LogicException('Pengajuan sudah diproses oleh atasan.');
            }

            $locked->fill([
                'status' => $decision === 'approve' ? LeaveRequestStatus::PendingHr : LeaveRequestStatus::Rejected,
                'supervisor_approved_by_id' => $approver->id,
                'supervisor_approved_at' => now(),
                'supervisor_notes' => $notes,
            ])->save();
        });
    }

    public function byHr(LeaveRequest $leaveRequest, Employee $approver, string $decision, ?string $notes): LeaveRequest
    {
        return $this->process($leaveRequest, function (LeaveRequest $locked) use ($approver, $decision, $notes): void {
            if ($locked->status !== LeaveRequestStatus::PendingHr) {
                throw new LogicException('Pengajuan belum siap atau sudah diproses oleh SDM.');
            }

            $locked->fill([
                'status' => $decision === 'approve' ? LeaveRequestStatus::Approved : LeaveRequestStatus::Rejected,
                'hr_approved_by_id' => $approver->id,
                'hr_approved_at' => now(),
                'hr_notes' => $notes,
            ])->save();
        });
    }

    private function process(LeaveRequest $leaveRequest, callable $callback): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest, $callback): LeaveRequest {
            $locked = LeaveRequest::lockForUpdate()->findOrFail($leaveRequest->id);
            $callback($locked);

            return $locked->refresh();
        });
    }
}
