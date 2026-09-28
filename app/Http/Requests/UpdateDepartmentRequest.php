<?php

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Validation\Validator;

class UpdateDepartmentRequest extends StoreDepartmentRequest
{
    public function rules(): array
    {
        return $this->departmentRules($this->route('department'));
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('parent_id') || $this->input('parent_id') === null) {
                return;
            }

            /** @var Department $department */
            $department = $this->route('department');
            $candidate = Department::find($this->integer('parent_id'));

            while ($candidate !== null) {
                if ($candidate->is($department)) {
                    $validator->errors()->add('parent_id', 'Unit induk tidak boleh berasal dari turunan departemen ini.');

                    return;
                }

                $candidate = $candidate->parent;
            }
        }];
    }
}
