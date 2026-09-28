<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $department = Department::firstOrCreate(
            ['code' => 'SDM'],
            ['name' => 'Biro Sumber Daya Manusia', 'type' => 'administration', 'is_active' => true],
        );
        $position = Position::firstOrCreate(
            ['name' => 'Administrator SDM'],
            ['category' => 'structural', 'level' => 1, 'is_active' => true],
        );
        $user = User::firstOrCreate(
            ['email' => 'admin@kampus.ac.id'],
            ['name' => 'Administrator SDM', 'password' => 'ADMIN001', 'role' => 'admin', 'must_change_password' => true],
        );

        Employee::updateOrCreate(
            ['nip' => 'ADMIN001'],
            [
                'user_id' => $user->id,
                'department_id' => $department->id,
                'position_id' => $position->id,
                'employee_number' => 'ADMIN001',
                'full_name' => 'Administrator SDM',
                'gender' => 'male',
                'email' => $user->email,
                'phone' => '080000000000',
                'employment_type' => 'permanent',
                'status' => 'active',
                'joined_on' => now()->startOfYear(),
                'date_of_birth' => '1990-01-01',
                'nik' => '0000000000000001',
                'identity_address' => 'Alamat administrator kampus',
                'residential_address' => 'Alamat administrator kampus',
                'last_education' => 'S1',
                'university' => 'Universitas Kampus',
                'study_program' => 'Sistem Informasi',
                'annual_leave_days' => 12,
                'mother_name' => 'Data belum dilengkapi',
                'base_salary' => 0,
                'transport_allowance' => 0,
            ],
        );
    }
}
