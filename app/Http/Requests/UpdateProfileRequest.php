<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'title_prefix' => ['nullable', 'string', 'max:50'],
            'title_suffix' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'phone' => ['required', 'string', 'max:30'],
            'identity_address' => ['required', 'string', 'max:2000'],
            'residential_address' => ['required', 'string', 'max:2000'],
            'last_education' => ['required', Rule::in(['SMA/SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'])],
            'university' => ['required', 'string', 'max:255'],
            'study_program' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
        ];
    }
}
