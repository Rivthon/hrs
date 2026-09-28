<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePayrollPeriodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage-users') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'period_date' => ['required', 'date_format:Y-m-d', 'unique:payroll_periods,period_date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $period = (string) $this->input('period');

        $this->merge(['period_date' => preg_match('/^\d{4}-\d{2}$/', $period) === 1 ? $period.'-01' : null]);
    }
}
