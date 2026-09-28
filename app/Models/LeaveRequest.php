<?php

namespace App\Models;

use App\Enums\LeaveRequestStatus;
use Database\Factories\LeaveRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id',
    'replacement_employee_id',
    'direct_supervisor_id',
    'start_date',
    'end_date',
    'total_working_days',
    'reason',
    'status',
    'submitted_at',
    'supervisor_approved_by_id',
    'supervisor_approved_at',
    'supervisor_notes',
    'hr_approved_by_id',
    'hr_approved_at',
    'hr_notes',
])]
class LeaveRequest extends Model
{
    /** @use HasFactory<LeaveRequestFactory> */
    use HasFactory;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function replacement(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'replacement_employee_id');
    }

    public function directSupervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'direct_supervisor_id');
    }

    public function supervisorApprover(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'supervisor_approved_by_id');
    }

    public function hrApprover(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'hr_approved_by_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'submitted_at' => 'datetime',
            'supervisor_approved_at' => 'datetime',
            'hr_approved_at' => 'datetime',
            'status' => LeaveRequestStatus::class,
            'total_working_days' => 'integer',
        ];
    }
}
