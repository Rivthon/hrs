<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncPublicHolidaysRequest extends FormRequest
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
     * @return array<string, array<int, string|int>>
     */
    public function rules(): array
    {
        return [
            'year' => ['required', 'integer', 'min:'.(now()->year - 1), 'max:'.(now()->year + 3)],
        ];
    }
}
