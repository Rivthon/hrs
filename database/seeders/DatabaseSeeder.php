<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $departments = Department::factory()->count(5)->create();
        $positions = Position::factory()->count(5)->create();

        Employee::factory()
            ->count(24)
            ->recycle($departments)
            ->recycle($positions)
            ->create();

        $this->call(AdminUserSeeder::class);
        $this->call([
            OrganizationStructureSeeder::class,
            PublicHolidaySeeder::class,
        ]);
    }
}
