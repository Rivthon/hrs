<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBusinessTripRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', Rule::exists('employees', 'id')->where('status', 'active')],
            'title' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:5000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'transportation' => ['nullable', 'string', 'max:255'],
            'allowance' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'assignment_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
