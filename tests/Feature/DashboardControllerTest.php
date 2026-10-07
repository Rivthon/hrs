<?php

namespace Tests\Feature;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Position;
use App\Models\PublicHoliday;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_workforce_summary(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $department = Department::factory()->create();
        $position = Position::factory()->create();
        Employee::factory()->count(2)->active()->for($department)->for($position)->create();
        Employee::factory()->for($department)->for($position)->create(['status' => 'inactive']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('statistics', [
                'employees' => 3,
                'active_employees' => 2,
                'lecturers' => 0,
                'staff' => 0,
                'departments' => 1,
                'positions' => 1,
                'pending_supervisor_leave' => 0,
                'pending_hr_leave' => 0,
                'approved_leave_this_month' => 0,
            ])
            ->assertSee('Ringkasan SDM Kampus')
            ->assertSee($department->name);
    }

    public function test_dashboard_only_counts_active_master_data(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Department::factory()->create();
        Department::factory()->create(['is_active' => false]);
        Position::factory()->create();
        Position::factory()->create(['is_active' => false]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertViewHas('statistics.departments', 1)
            ->assertViewHas('statistics.positions', 1);
    }

    public function test_admin_dashboard_shows_actionable_leave_payroll_and_holiday_information(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lecturerUser = User::factory()->create(['role' => 'dosen']);
        $department = Department::factory()->create(['name' => 'Farmasi']);
        $position = Position::factory()->create();
        $lecturer = Employee::factory()->active()->for($lecturerUser)->for($department)->for($position)->create();
        $replacement = Employee::factory()->active()->for($department)->for($position)->create();
        LeaveRequest::factory()->create([
            'employee_id' => $lecturer->id,
            'replacement_employee_id' => $replacement->id,
            'direct_supervisor_id' => $replacement->id,
            'status' => LeaveRequestStatus::PendingHr,
        ]);
        $payrollPeriod = PayrollPeriod::factory()->create(['period_date' => '2026-10-01']);
        Payroll::factory()->for($payrollPeriod, 'period')->for($lecturer)->create(['net_salary' => 5500000]);
        PublicHoliday::factory()->create([
            'holiday_date' => now()->addWeek()->toDateString(),
            'name' => 'Libur Kampus',
        ]);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('statistics.pending_hr_leave', 1)
            ->assertViewHas('statistics.lecturers', 1)
            ->assertSeeText('Pengajuan Cuti Terbaru')
            ->assertSeeText('Payroll terbaru')
            ->assertSeeText('Rp 5.500.000')
            ->assertSeeText('Libur Kampus')
            ->assertSeeText('Farmasi');
    }

    public function test_staff_dashboard_does_not_expose_admin_payroll_summary(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        Employee::factory()->for($staff)->create();
        $payrollPeriod = PayrollPeriod::factory()->create();
        Payroll::factory()->for($payrollPeriod, 'period')->create(['net_salary' => 99000000]);

        $this->actingAs($staff)->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Dashboard Tendik')
            ->assertDontSeeText('Payroll terbaru')
            ->assertDontSeeText('Rp 99.000.000');
    }

    public function test_dashboard_popup_only_shows_employees_currently_on_approved_leave(): void
    {
        $this->travelTo('2026-10-07 10:00:00');
        $admin = User::factory()->create(['role' => 'admin']);
        $lecturerUser = User::factory()->create(['role' => 'dosen']);
        $department = Department::factory()->create();
        $position = Position::factory()->create();
        $lecturer = Employee::factory()->active()->for($lecturerUser)->for($department)->for($position)->create(['full_name' => 'Dosen Sedang Cuti']);
        $finishedEmployee = Employee::factory()->active()->for($department)->for($position)->create(['full_name' => 'Pegawai Selesai Cuti']);
        $replacement = Employee::factory()->active()->for($department)->for($position)->create(['full_name' => 'Budi Pegawai Pengganti']);
        LeaveRequest::factory()->create([
            'employee_id' => $lecturer->id,
            'replacement_employee_id' => $replacement->id,
            'status' => LeaveRequestStatus::Approved,
            'leave_type' => LeaveType::Annual,
            'start_date' => '2026-10-06',
            'end_date' => '2026-10-08',
        ]);
        LeaveRequest::factory()->create([
            'employee_id' => $finishedEmployee->id,
            'status' => LeaveRequestStatus::Approved,
            'leave_type' => LeaveType::Annual,
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-06',
        ]);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Tendik atau Dosen yang sedang Cuti Hari Ini')
            ->assertSeeText('Dosen Sedang Cuti')
            ->assertSeeText('Pengganti: Budi Pegawai Pengganti')
            ->assertViewHas('employeesOnLeaveToday', fn ($leaveRequests): bool => $leaveRequests->pluck('employee_id')->all() === [$lecturer->id]);
    }

    public function test_finished_hourly_leave_is_not_shown_in_dashboard_popup(): void
    {
        $this->travelTo('2026-10-07 15:00:00');
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = Employee::factory()->active()->create(['full_name' => 'Izin Sudah Selesai']);
        LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'status' => LeaveRequestStatus::Approved,
            'leave_type' => LeaveType::Hourly,
            'start_date' => '2026-10-07',
            'end_date' => '2026-10-07',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'duration_minutes' => 120,
            'total_working_days' => 0,
        ]);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSeeText('Tendik atau Dosen yang sedang Cuti Hari Ini');
    }

    public function test_replacement_employee_sees_leave_assignment_notification(): void
    {
        $replacementUser = User::factory()->create(['role' => 'staff']);
        $replacement = Employee::factory()->active()->for($replacementUser)->create();
        $applicant = Employee::factory()->active()->create(['full_name' => 'Siti Pengaju Cuti']);
        LeaveRequest::factory()->create([
            'employee_id' => $applicant->id,
            'replacement_employee_id' => $replacement->id,
            'status' => LeaveRequestStatus::PendingSupervisor,
            'start_date' => today()->addDay(),
            'end_date' => today()->addDays(2),
        ]);

        $this->actingAs($replacementUser)->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Notifikasi Pengganti Cuti')
            ->assertSeeText('Siti Pengaju Cuti mengajukan cuti dan Anda menjadi penggantinya.');
    }
}
