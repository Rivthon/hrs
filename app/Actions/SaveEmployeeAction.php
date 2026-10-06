<?php

namespace App\Actions;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SaveEmployeeAction
{
    /**
     * Create a new class instance.
     */
    public function handle(array $data, ?Employee $employee = null): Employee
    {
        return DB::transaction(function () use ($data, $employee): Employee {
            $employee ??= new Employee;

            $user = $employee->user ?? new User;
            $isNewUser = ! $user->exists;
            $user->fill([
                'name' => $data['full_name'],
                'email' => $data['email'],
                'role' => $data['role'],
            ]);

            if ($isNewUser) {
                $user->password = $data['nip'];
                $user->must_change_password = true;
            }

            $user->save();

            $employeeData = Arr::except($data, ['role', 'same_as_identity_address']);
            $employeeData['user_id'] = $user->id;
            $employeeData['employee_number'] = $data['nip'];

            if ((bool) ($data['same_as_identity_address'] ?? false)) {
                $employeeData['residential_address'] = $data['identity_address'];
            }

            $employee->fill($employeeData)->save();

            return $employee->refresh();
        });
    }
}
