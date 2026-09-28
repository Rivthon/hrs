<?php

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->departmentRules();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function departmentRules(?Department $department = null): array
    {
        return [
            'parent_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'code' => ['required', 'string', 'max:20', Rule::unique('departments')->ignore($department)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['leadership', 'faculty', 'program_study', 'administration', 'support', 'work_unit'])],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => Str::upper(trim((string) $this->input('code'))),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function attributes(): array
    {
        return [
            'parent_id' => 'unit induk',
            'code' => 'kode departemen',
            'name' => 'nama departemen',
            'type' => 'jenis unit',
            'is_active' => 'status',
        ];
    }
}
