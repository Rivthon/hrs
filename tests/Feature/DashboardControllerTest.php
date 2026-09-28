<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_workforce_summary(): void
    {
        $department = Department::factory()->create();
        $position = Position::factory()->create();
        Employee::factory()->count(2)->active()->for($department)->for($position)->create();
        Employee::factory()->for($department)->for($position)->create(['status' => 'inactive']);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('statistics', [
                'employees' => 3,
                'active_employees' => 2,
                'departments' => 1,
                'positions' => 1,
            ])
            ->assertSee('Ringkasan SDM Kampus')
            ->assertSee($department->name);
    }

    public function test_dashboard_only_counts_active_master_data(): void
    {
        Department::factory()->create();
        Department::factory()->create(['is_active' => false]);
        Position::factory()->create();
        Position::factory()->create(['is_active' => false]);

        $this->get(route('dashboard'))
            ->assertViewHas('statistics.departments', 1)
            ->assertViewHas('statistics.positions', 1);
    }
}
