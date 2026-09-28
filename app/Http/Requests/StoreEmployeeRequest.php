<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
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
        return $this->employeeRules();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function employeeRules(?Employee $employee = null): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'title_prefix' => ['nullable', 'string', 'max:50'],
            'title_suffix' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'joined_on' => ['required', 'date', 'before_or_equal:today'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'nik' => ['required', 'digits_between:16,20', Rule::unique('employees')->ignore($employee)],
            'npwp' => ['nullable', 'string', 'max:40', Rule::unique('employees')->ignore($employee)],
            'bpjs_health_number' => ['nullable', 'string', 'max:40', Rule::unique('employees')->ignore($employee)],
            'bpjs_employment_number' => ['nullable', 'string', 'max:40', Rule::unique('employees')->ignore($employee)],
            'nidn' => ['nullable', 'required_if:role,dosen', 'string', 'max:30', Rule::unique('employees')->ignore($employee)],
            'nip' => ['required', 'string', 'max:30', Rule::unique('employees')->ignore($employee)],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')->where('is_active', true)],
            'position_id' => ['required', 'integer', Rule::exists('positions', 'id')->where('is_active', true)],
            'phone' => ['required', 'string', 'max:30'],
            'identity_address' => ['required', 'string', 'max:2000'],
            'same_as_identity_address' => ['nullable', 'boolean'],
            'residential_address' => ['nullable', 'required_unless:same_as_identity_address,1', 'string', 'max:2000'],
            'last_education' => ['required', Rule::in(['SMA/SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'])],
            'university' => ['required', 'string', 'max:255'],
            'study_program' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('employees')->ignore($employee), Rule::unique('users')->ignore($employee?->user_id)],
            'annual_leave_days' => ['required', 'integer', 'min:0', 'max:365'],
            'supervisor_id' => ['nullable', 'integer', Rule::exists('employees', 'id'), Rule::notIn(array_filter([$employee?->id]))],
            'mother_name' => ['required', 'string', 'max:255'],
            'base_salary' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'transport_allowance' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'role' => ['required', Rule::in(['admin', 'hr', 'dosen', 'staff'])],
            'employment_type' => ['required', Rule::in(['permanent', 'contract', 'part_time'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name' => 'nama lengkap',
            'joined_on' => 'tanggal masuk',
            'date_of_birth' => 'tanggal lahir',
            'department_id' => 'departemen',
            'position_id' => 'jabatan',
            'phone' => 'nomor HP/WhatsApp',
            'identity_address' => 'alamat KTP',
            'residential_address' => 'alamat rumah',
            'last_education' => 'pendidikan terakhir',
            'annual_leave_days' => 'jatah cuti',
            'supervisor_id' => 'atasan langsung',
            'mother_name' => 'nama ibu kandung',
            'base_salary' => 'gaji pokok',
            'transport_allowance' => 'tunjangan transport',
        ];
    }
}
