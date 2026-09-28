<?php

namespace App\Http\Requests;

use App\Models\Employee;

class UpdateEmployeeRequest extends StoreEmployeeRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function rules(): array
    {
        /** @var Employee $employee */
        $employee = $this->route('employee');

        return $this->employeeRules($employee);
    }
}
