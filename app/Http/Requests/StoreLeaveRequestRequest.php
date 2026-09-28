<?php

namespace App\Http\Requests;

use App\Enums\LeaveRequestStatus;
use App\Models\LeaveRequest;
use App\Services\WorkingDayCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreLeaveRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->employee !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'replacement_employee_id' => [
                'required', 'integer',
                Rule::exists('employees', 'id')->where('status', 'active'),
                Rule::notIn([$this->user()?->employee?->id]),
            ],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['start_date', 'end_date'])) {
                return;
            }

            $startDate = CarbonImmutable::parse($this->string('start_date'));
            $endDate = CarbonImmutable::parse($this->string('end_date'));
            $workingDays = (new WorkingDayCalculator)->count($startDate, $endDate);

            if ($workingDays < 1) {
                $validator->errors()->add('end_date', 'Rentang cuti harus memiliki minimal satu hari kerja.');

                return;
            }

            if ($workingDays > 3) {
                $validator->errors()->add('end_date', 'Pengajuan cuti maksimal tiga hari kerja.');
            }

            $employee = $this->user()->employee;
            if ($employee->supervisor_id === null) {
                $validator->errors()->add('start_date', 'Atasan langsung belum ditentukan pada profil Anda.');
            }

            $reservedDays = LeaveRequest::query()
                ->whereBelongsTo($employee)
                ->whereYear('start_date', $startDate->year)
                ->whereIn('status', [LeaveRequestStatus::PendingSupervisor, LeaveRequestStatus::PendingHr, LeaveRequestStatus::Approved])
                ->sum('total_working_days');

            if ($reservedDays + $workingDays > $employee->annual_leave_days) {
                $validator->errors()->add('end_date', 'Jatah cuti tahunan tidak mencukupi.');
            }

            $hasOverlap = LeaveRequest::query()
                ->whereBelongsTo($employee)
                ->whereIn('status', [LeaveRequestStatus::PendingSupervisor, LeaveRequestStatus::PendingHr, LeaveRequestStatus::Approved])
                ->whereDate('start_date', '<=', $endDate)
                ->whereDate('end_date', '>=', $startDate)
                ->exists();

            if ($hasOverlap) {
                $validator->errors()->add('start_date', 'Tanggal cuti bertumpang tindih dengan pengajuan lain.');
            }
        }];
    }

    public function workingDays(): int
    {
        return (new WorkingDayCalculator)->count(
            CarbonImmutable::parse($this->validated('start_date')),
            CarbonImmutable::parse($this->validated('end_date')),
        );
    }

    public function attributes(): array
    {
        return [
            'replacement_employee_id' => 'pegawai pengganti',
            'start_date' => 'tanggal mulai',
            'end_date' => 'tanggal selesai',
            'reason' => 'alasan cuti',
        ];
    }
}
