<?php

namespace App\Actions;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaveEmployeeAction
{
    public function __construct(private readonly RecordAuditLogAction $recordAuditLog) {}

    /**
     * Create a new class instance.
     */
    public function handle(array $data, ?Employee $employee = null, ?string $initialPassword = null): Employee
    {
        return DB::transaction(function () use ($data, $employee, $initialPassword): Employee {
            $employee ??= new Employee;
            $isNewEmployee = ! $employee->exists;
            $oldEmployeeValues = $employee->getAttributes();

            $user = $employee->user ?? new User;
            $isNewUser = ! $user->exists;
            $oldUserValues = $user->only(['name', 'email', 'role']);
            $user->fill([
                'name' => $data['full_name'],
                'email' => $data['email'],
                'role' => $data['role'],
            ]);

            if ($isNewUser) {
                $user->password = $initialPassword ?? Str::password(16);
                $user->must_change_password = false;
            }

            $user->save();

            $employeeData = Arr::except($data, ['role', 'same_as_identity_address']);
            $employeeData['user_id'] = $user->id;
            $employeeData['employee_number'] = $data['nip'];

            if ((bool) ($data['same_as_identity_address'] ?? false)) {
                $employeeData['residential_address'] = $data['identity_address'];
            }

            $employee->fill($employeeData)->save();

            $changedEmployeeValues = Arr::except($employee->getChanges(), ['updated_at']);
            $changedUserValues = Arr::except($user->getChanges(), ['password', 'updated_at']);
            $oldValues = collect(array_keys($changedEmployeeValues))
                ->mapWithKeys(fn (string $key): array => [$key => $oldEmployeeValues[$key] ?? null])
                ->merge(collect(array_keys($changedUserValues))->mapWithKeys(fn (string $key): array => ["user.{$key}" => $oldUserValues[$key] ?? null]))
                ->all();
            $newValues = collect($changedEmployeeValues)
                ->merge(collect($changedUserValues)->mapWithKeys(fn (mixed $value, string $key): array => ["user.{$key}" => $value]))
                ->all();

            $this->recordAuditLog->handle(
                $isNewEmployee ? 'employee.created' : 'employee.updated',
                $employee,
                ($isNewEmployee ? 'Menambahkan' : 'Memperbarui').' data pegawai '.$employee->full_name,
                $oldValues,
                $newValues,
            );

            return $employee->refresh();
        });
    }
}
