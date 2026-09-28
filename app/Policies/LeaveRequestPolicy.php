<?php

namespace App\Policies;

use App\Enums\LeaveRequestStatus;
use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->employee !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->employee?->is($leaveRequest->employee)
            || $user->employee?->is($leaveRequest->directSupervisor)
            || in_array($user->role, ['admin', 'hr'], true);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->employee !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LeaveRequest $leaveRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LeaveRequest $leaveRequest): bool
    {
        return false;
    }

    public function supervisorApprove(User $user, LeaveRequest $leaveRequest): bool
    {
        return $leaveRequest->status === LeaveRequestStatus::PendingSupervisor
            && $user->employee?->is($leaveRequest->directSupervisor);
    }

    public function hrApprove(User $user, LeaveRequest $leaveRequest): bool
    {
        return $leaveRequest->status === LeaveRequestStatus::PendingHr
            && in_array($user->role, ['admin', 'hr'], true)
            && $user->employee !== null;
    }
}
