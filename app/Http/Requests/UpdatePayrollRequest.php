<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatePayrollRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $moneyFields = [
            'position_allowance', 'functional_allowance', 'teaching_honor', 'proctoring_honor',
            'final_seminar_honor', 'thesis_defense_honor', 'thesis_supervisor_honor',
            'pkk_supervision_honor', 'practical_exam_honor', 'duty_honor',
            'bpjs_employment_deduction', 'bpjs_health_deduction', 'income_tax_deduction',
            'transport_deduction', 'lateness_deduction',
        ];

        $normalized = collect($moneyFields)
            ->filter(fn (string $field): bool => $this->has($field))
            ->mapWithKeys(fn (string $field): array => [
                $field => preg_replace('/\D/', '', (string) $this->input($field)) ?: '0',
            ])
            ->all();

        $this->merge($normalized);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage-users')
            && $this->route('payroll')?->period?->status === 'draft';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $moneyRules = ['required', 'numeric', 'min:0', 'max:9999999999999.99'];
        $roleMoneyRules = ['sometimes', 'numeric', 'min:0', 'max:9999999999999.99'];

        return [
            'position_allowance' => $moneyRules,
            'functional_allowance' => $moneyRules,
            'teaching_honor' => $roleMoneyRules,
            'proctoring_honor' => $roleMoneyRules,
            'final_seminar_honor' => $roleMoneyRules,
            'thesis_defense_honor' => $roleMoneyRules,
            'thesis_supervisor_honor' => $roleMoneyRules,
            'pkk_supervision_honor' => $moneyRules,
            'practical_exam_honor' => $moneyRules,
            'duty_honor' => $moneyRules,
            'bpjs_employment_deduction' => $moneyRules,
            'bpjs_health_deduction' => $moneyRules,
            'income_tax_deduction' => $moneyRules,
            'transport_deduction' => $moneyRules,
            'lateness_deduction' => $moneyRules,
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $isLecturer = $this->route('payroll')->employee->user?->role === 'dosen';
            $lecturerFields = ['teaching_honor', 'final_seminar_honor', 'thesis_defense_honor', 'thesis_supervisor_honor'];

            if (! $isLecturer && collect($lecturerFields)->contains(fn (string $field): bool => (float) $this->input($field) > 0)) {
                $validator->errors()->add('teaching_honor', 'Honor dosen hanya dapat diisi untuk pegawai dengan role dosen.');
            }

            if ($isLecturer && (float) $this->input('proctoring_honor') > 0) {
                $validator->errors()->add('proctoring_honor', 'Honor mengawas hanya dapat diisi untuk tenaga kependidikan.');
            }
        }];
    }

    public function attributes(): array
    {
        return [
            'position_allowance' => 'tunjangan jabatan',
            'functional_allowance' => 'tunjangan fungsional',
            'teaching_honor' => 'honor mengajar',
            'proctoring_honor' => 'honor mengawas',
            'income_tax_deduction' => 'PPh Pasal 21',
            'lateness_deduction' => 'potongan keterlambatan',
        ];
    }
}
