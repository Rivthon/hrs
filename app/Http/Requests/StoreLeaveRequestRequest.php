<?php

namespace App\Http\Requests;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveType;
use App\Models\LeaveRequest;
use App\Services\WorkingDayCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->employee !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'leave_type' => ['required', new Enum(LeaveType::class)],
            'replacement_employee_id' => [
                'required', 'integer',
                Rule::exists('employees', 'id')->where('status', 'active'),
                Rule::notIn([$this->user()?->employee?->id]),
            ],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', Rule::requiredIf($this->isPartialDay()), 'date_format:H:i'],
            'end_time' => ['nullable', Rule::requiredIf($this->isPartialDay()), 'date_format:H:i'],
            'reason' => ['required', 'string', 'max:2000'],
            'supporting_document' => [
                'nullable', Rule::requiredIf($this->input('leave_type') === LeaveType::Sick->value),
                'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120',
            ],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['leave_type', 'start_date', 'end_date', 'start_time', 'end_time'])) {
                return;
            }

            $leaveType = LeaveType::from($this->string('leave_type')->toString());
            $startDate = CarbonImmutable::parse($this->string('start_date'));
            $endDate = CarbonImmutable::parse($this->string('end_date'));
            $workingDays = (new WorkingDayCalculator)->count($startDate, $endDate);

            if ($workingDays < 1) {
                $validator->errors()->add('end_date', 'Rentang izin harus memiliki minimal satu hari kerja.');

                return;
            }

            if ($leaveType === LeaveType::Annual && $workingDays > 3) {
                $validator->errors()->add('end_date', 'Pengajuan cuti tahunan maksimal tiga hari kerja.');
            }

            if ($leaveType === LeaveType::Maternity && $this->user()->employee->gender !== 'female') {
                $validator->errors()->add('leave_type', 'Cuti melahirkan hanya dapat diajukan oleh pegawai perempuan.');
            }

            if ($leaveType->isPartialDay()) {
                $this->validatePartialDay($validator, $leaveType, $startDate, $endDate);
            }

            $employee = $this->user()->employee;
            if ($employee->supervisor_id === null) {
                $validator->errors()->add('start_date', 'Atasan langsung belum ditentukan pada profil Anda.');
            }

            if ($leaveType === LeaveType::Annual) {
                $reservedDays = LeaveRequest::query()
                    ->whereBelongsTo($employee)
                    ->where('leave_type', LeaveType::Annual->value)
                    ->whereYear('start_date', $startDate->year)
                    ->whereIn('status', [LeaveRequestStatus::PendingSupervisor, LeaveRequestStatus::PendingHr, LeaveRequestStatus::Approved])
                    ->sum('total_working_days');

                if ($reservedDays + $workingDays > $employee->annual_leave_days) {
                    $validator->errors()->add('end_date', 'Jatah cuti tahunan tidak mencukupi.');
                }
            }

            $hasOverlap = LeaveRequest::query()
                ->whereBelongsTo($employee)
                ->whereIn('status', [LeaveRequestStatus::PendingSupervisor, LeaveRequestStatus::PendingHr, LeaveRequestStatus::Approved])
                ->whereDate('start_date', '<=', $endDate)
                ->whereDate('end_date', '>=', $startDate)
                ->exists();

            if ($hasOverlap) {
                $validator->errors()->add('start_date', 'Tanggal izin bertumpang tindih dengan pengajuan lain.');
            }
        }];
    }

    public function workingDays(): int
    {
        if ($this->leaveType()->isPartialDay()) {
            return 0;
        }

        return (new WorkingDayCalculator)->count(
            CarbonImmutable::parse($this->validated('start_date')),
            CarbonImmutable::parse($this->validated('end_date')),
        );
    }

    public function durationMinutes(): ?int
    {
        if (! $this->leaveType()->isPartialDay()) {
            return null;
        }

        $date = $this->validated('start_date');

        return (int) CarbonImmutable::parse($date.' '.$this->validated('start_time'))
            ->diffInMinutes(CarbonImmutable::parse($date.' '.$this->validated('end_time')), true);
    }

    public function leaveType(): LeaveType
    {
        return LeaveType::from($this->validated('leave_type'));
    }

    public function attributes(): array
    {
        return [
            'leave_type' => 'jenis cuti/izin',
            'replacement_employee_id' => 'pegawai pengganti',
            'start_date' => 'tanggal mulai',
            'end_date' => 'tanggal selesai',
            'start_time' => 'jam mulai',
            'end_time' => 'jam selesai',
            'reason' => 'alasan cuti/izin',
            'supporting_document' => 'surat dokter',
        ];
    }

    private function isPartialDay(): bool
    {
        return in_array($this->input('leave_type'), [LeaveType::HalfDay->value, LeaveType::Hourly->value], true);
    }

    private function validatePartialDay(Validator $validator, LeaveType $leaveType, CarbonImmutable $startDate, CarbonImmutable $endDate): void
    {
        if (! $startDate->isSameDay($endDate)) {
            $validator->errors()->add('end_date', 'Izin setengah hari atau per jam harus diajukan pada tanggal yang sama.');

            return;
        }

        $startTime = CarbonImmutable::parse($startDate->toDateString().' '.$this->string('start_time'));
        $endTime = CarbonImmutable::parse($startDate->toDateString().' '.$this->string('end_time'));

        if ($endTime->lessThanOrEqualTo($startTime)) {
            $validator->errors()->add('end_time', 'Jam selesai harus setelah jam mulai.');

            return;
        }

        $durationMinutes = (int) $startTime->diffInMinutes($endTime, true);

        if ($leaveType === LeaveType::HalfDay && $durationMinutes !== 240) {
            $validator->errors()->add('end_time', 'Izin setengah hari harus berdurasi tepat empat jam.');
        }

        if ($leaveType === LeaveType::Hourly && $durationMinutes > 240) {
            $validator->errors()->add('end_time', 'Izin per jam maksimal empat jam.');
        }
    }
}
