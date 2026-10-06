<?php

namespace App\Actions;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncPasLecturersAction
{
    /** @return array{created: int, updated: int} */
    public function handle(): array
    {
        $departmentIds = Department::query()
            ->whereIn('code', ['DOSEN-FARMASI', 'DOSEN-GIZI', 'DOSEN-KEBIDANAN'])
            ->pluck('id', 'code');
        $position = Position::firstOrCreate(
            ['name' => 'Dosen'],
            ['category' => 'support', 'is_active' => true],
        );
        $lecturers = DB::connection('pas')->table('dosen')->orderBy('dosen_id')->get();
        $created = 0;
        $updated = 0;

        foreach ($lecturers as $lecturer) {
            DB::transaction(function () use ($lecturer, $departmentIds, $position, &$created, &$updated): void {
                $employee = Employee::query()
                    ->where('pas_dosen_id', $lecturer->dosen_id)
                    ->when(filled($lecturer->nidn), fn ($query) => $query->orWhere('nidn', $lecturer->nidn))
                    ->orWhere('email', $lecturer->email)
                    ->first();
                $isNew = ! $employee;
                $employee ??= new Employee;
                $user = $employee->user ?? User::where('email', $lecturer->email)->first() ?? new User;

                $user->forceFill([
                    'name' => $lecturer->nama,
                    'email' => $lecturer->email,
                    'password' => $lecturer->password ?: Str::password(32),
                    'role' => 'dosen',
                    'must_change_password' => false,
                ])->save();

                $departmentCode = match ((string) $lecturer->jurusan_id) {
                    '48201' => 'DOSEN-FARMASI',
                    '13211' => 'DOSEN-GIZI',
                    '15401' => 'DOSEN-KEBIDANAN',
                    default => 'DOSEN-KEBIDANAN',
                };

                $employee->fill([
                    'user_id' => $user->id,
                    'pas_dosen_id' => $lecturer->dosen_id,
                    'pas_kode_dosen' => $lecturer->kd_dosen,
                    'department_id' => $departmentIds[$departmentCode],
                    'position_id' => $position->id,
                    'employee_number' => $lecturer->kd_dosen ?: 'PAS-'.$lecturer->dosen_id,
                    'full_name' => $lecturer->nama,
                    'gender' => $lecturer->jenis_kelamin === 'L' ? 'male' : 'female',
                    'email' => $lecturer->email,
                    'phone' => $lecturer->no_telp,
                    'identity_address' => $lecturer->alamat,
                    'residential_address' => $lecturer->alamat,
                    'employment_type' => $lecturer->status_dosen === 'Dosen Tetap' ? 'permanent' : 'contract',
                    'status' => 'active',
                    'joined_on' => $lecturer->created_at ?: now()->toDateString(),
                    'date_of_birth' => $lecturer->tanggal_lahir,
                    'nidn' => filled($lecturer->nidn) ? $lecturer->nidn : null,
                    'nip' => $lecturer->kd_dosen,
                    'study_program' => match ((string) $lecturer->jurusan_id) {
                        '48201' => 'Farmasi',
                        '13211' => 'Gizi',
                        '15401' => 'Kebidanan',
                        default => null,
                    },
                    'annual_leave_days' => $employee->annual_leave_days ?: 12,
                    'base_salary' => $employee->base_salary ?: 0,
                    'transport_allowance' => $employee->transport_allowance ?: 0,
                ])->save();

                $isNew ? $created++ : $updated++;
            });
        }

        return compact('created', 'updated');
    }
}
