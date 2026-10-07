<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeTodo;
use App\Models\User;
use App\Models\WorkReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_sees_personal_workspace_on_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $employee = Employee::factory()->for($user)->create(['full_name' => 'Siti Tendik']);
        EmployeeTodo::factory()->for($employee)->create(['title' => 'Siapkan laporan unit']);
        WorkReport::factory()->for($employee)->create(['title' => 'Rekap data mahasiswa']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Dashboard Tendik')
            ->assertSeeText('Siti Tendik')
            ->assertSeeText('Siapkan laporan unit')
            ->assertSeeText('Rekap data mahasiswa')
            ->assertSeeText('Ajukan Cuti')
            ->assertDontSeeText('Laporan BAP Dosen');
    }

    public function test_lecturer_sees_pas_bap_section_on_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'dosen']);
        Employee::factory()->for($user)->create(['full_name' => 'Dosen Farmasi', 'pas_dosen_id' => null]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Dashboard Dosen')
            ->assertSeeText('Laporan BAP Dosen')
            ->assertSeeText('Data BAP belum dapat dimuat');
    }

    public function test_employee_can_create_and_complete_own_todo(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $employee = Employee::factory()->for($user)->create();

        $this->actingAs($user)->post(route('todos.store'), [
            'title' => 'Menyusun laporan bulanan',
            'due_date' => '2026-10-20',
            'priority' => 'high',
        ])->assertRedirect();

        $todo = EmployeeTodo::query()->sole();
        $this->assertSame($employee->id, $todo->employee_id);
        $this->assertSame('Menyusun laporan bulanan', $todo->title);

        $this->actingAs($user)->put(route('todos.update', $todo))->assertRedirect();
        $this->assertTrue($todo->refresh()->is_completed);
        $this->assertNotNull($todo->completed_at);
    }

    public function test_employee_cannot_change_another_employees_todo(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        Employee::factory()->for($user)->create();
        $otherTodo = EmployeeTodo::factory()->create();

        $this->actingAs($user)->put(route('todos.update', $otherTodo))->assertForbidden();

        $this->assertFalse($otherTodo->refresh()->is_completed);
    }

    public function test_employee_can_create_work_report_but_cannot_delete_another_employees_report(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $employee = Employee::factory()->for($user)->create();
        $otherReport = WorkReport::factory()->create();

        $this->actingAs($user)->post(route('work-reports.store'), [
            'report_date' => today()->toDateString(),
            'title' => 'Rekap kegiatan harian',
            'description' => 'Melakukan rekap kegiatan dan dokumen unit kerja.',
            'status' => 'completed',
        ])->assertRedirect();

        $this->assertDatabaseHas('work_reports', [
            'employee_id' => $employee->id,
            'title' => 'Rekap kegiatan harian',
        ]);

        $this->actingAs($user)->delete(route('work-reports.destroy', $otherReport))->assertForbidden();
        $this->assertModelExists($otherReport);
    }

    public function test_todo_and_work_report_require_valid_input(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        Employee::factory()->for($user)->create();

        $this->actingAs($user)->post(route('todos.store'), [])->assertSessionHasErrors(['title', 'priority']);
        $this->actingAs($user)->post(route('work-reports.store'), [])->assertSessionHasErrors(['report_date', 'title', 'description', 'status']);

        $this->assertDatabaseCount('employee_todos', 0);
        $this->assertDatabaseCount('work_reports', 0);
    }
}
