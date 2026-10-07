<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessTripResponseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $businessTrip = $this->route('businessTrip');

        return $businessTrip
            && $businessTrip->employee_id === $this->user()?->employee?->id
            && $businessTrip->status === 'assigned';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:accepted,rejected'],
            'rejection_reason' => ['nullable', 'required_if:decision,rejected', 'string', 'max:2000'],
        ];
    }
}
