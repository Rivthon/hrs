<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessTripReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $businessTrip = $this->route('businessTrip');

        return $businessTrip
            && $businessTrip->employee_id === $this->user()?->employee?->id
            && in_array($businessTrip->status, ['accepted', 'reported'], true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'report_summary' => ['required', 'string', 'max:5000'],
            'report_result' => ['required', 'string', 'max:5000'],
            'report_notes' => ['nullable', 'string', 'max:5000'],
            'report_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
