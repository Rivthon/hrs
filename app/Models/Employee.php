<?php

namespace App\Models;

use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'pas_dosen_id',
    'pas_kode_dosen',
    'department_id',
    'position_id',
    'supervisor_id',
    'employee_number',
    'full_name',
    'title_prefix',
    'title_suffix',
    'gender',
    'email',
    'phone',
    'employment_type',
    'status',
    'joined_on',
    'date_of_birth',
    'nik',
    'npwp',
    'bpjs_health_number',
    'bpjs_employment_number',
    'nidn',
    'nip',
    'identity_address',
    'residential_address',
    'last_education',
    'university',
    'study_program',
    'annual_leave_days',
    'mother_name',
    'base_salary',
    'transport_allowance',
])]
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supervisor_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(self::class, 'supervisor_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function supervisedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'direct_supervisor_id');
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function todos(): HasMany
    {
        return $this->hasMany(EmployeeTodo::class);
    }

    public function workReports(): HasMany
    {
        return $this->hasMany(WorkReport::class);
    }

    public function businessTrips(): HasMany
    {
        return $this->hasMany(BusinessTrip::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return collect([$this->title_prefix, $this->full_name, $this->title_suffix])
            ->filter()
            ->join(' ');
    }

    public function getLengthOfServiceAttribute(): string
    {
        $interval = $this->joined_on->diff(now());

        return "{$interval->y} tahun, {$interval->m} bulan, {$interval->d} hari";
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'joined_on' => 'date',
            'date_of_birth' => 'date',
            'annual_leave_days' => 'integer',
            'base_salary' => 'decimal:2',
            'transport_allowance' => 'decimal:2',
        ];
    }
}
