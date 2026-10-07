<?php

namespace Tests\Feature;

use App\Enums\LeaveRequestStatus;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PublicHoliday;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LeaveRequestWorkflowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_employee_can_submit_leave_with_weekend_and_holiday_excluded(): void
    {
        $this->travelTo('2026-09-28');
        $supervisor = $this->employeeWithUser();
        $applicant = $this->employeeWithUser(['supervisor_id' => $supervisor->id]);
        $replacement = $this->employeeWithUser();
        PublicHoliday::factory()->create(['holiday_date' => '2026-10-05']);

        $response = $this->actingAs($applicant->user)->post(route('leave-requests.store'), [
            'replacement_employee_id' => $replacement->id,
            'start_date' => '2026-10-02',
            'end_date' => '2026-10-06',
            'reason' => 'Keperluan keluarga yang tidak dapat ditinggalkan.',
        ]);

        $response->assertSessionHasNoErrors();
        $leaveRequest = LeaveRequest::firstOrFail();
        $response->assertRedirect(route('leave-requests.show', $leaveRequest));
        $this->assertSame(2, $leaveRequest->total_working_days);
        $this->assertSame(LeaveRequestStatus::PendingSupervisor, $leaveRequest->status);
        $this->assertTrue($leaveRequest->directSupervisor->is($supervisor));
    }

    public function test_request_over_three_working_days_is_rejected(): void
    {
        $this->travelTo('2026-09-28');
        $supervisor = $this->employeeWithUser();
        $applicant = $this->employeeWithUser(['supervisor_id' => $supervisor->id]);
        $replacement = $this->employeeWithUser();

        $this->actingAs($applicant->user)->post(route('leave-requests.store'), [
            'replacement_employee_id' => $replacement->id,
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-09',
            'reason' => 'Keperluan keluarga yang tidak dapat ditinggalkan.',
        ])->assertSessionHasErrors('end_date');

        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_direct_supervisor_then_hr_can_approve_leave(): void
    {
        $supervisor = $this->employeeWithUser();
        $applicant = $this->employeeWithUser(['supervisor_id' => $supervisor->id]);
        $replacement = $this->employeeWithUser();
        $hr = $this->employeeWithUser([], 'hr');
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $applicant->id,
            'replacement_employee_id' => $replacement->id,
            'direct_supervisor_id' => $supervisor->id,
        ]);

        $this->actingAs($supervisor->user)->put(route('leave-requests.supervisor-approval', $leaveRequest), [
            'decision' => 'approve',
            'notes' => 'Pekerjaan sudah dialihkan.',
        ])->assertRedirect(route('leave-requests.index'));
        $this->assertSame(LeaveRequestStatus::PendingHr, $leaveRequest->refresh()->status);
        $this->assertSame($supervisor->id, $leaveRequest->supervisor_approved_by_id);

        $this->actingAs($hr->user)->put(route('leave-requests.hr-approval', $leaveRequest), [
            'decision' => 'approve',
            'notes' => 'Jatah cuti tersedia.',
        ])->assertRedirect(route('leave-requests.index'));
        $this->assertSame(LeaveRequestStatus::Approved, $leaveRequest->refresh()->status);
        $this->assertSame($hr->id, $leaveRequest->hr_approved_by_id);
        $this->assertSame(12, $applicant->refresh()->annual_leave_days);

        $this->actingAs($applicant->user)->get(route('leave-requests.index'))
            ->assertOk()
            ->assertSeeText('Sisa Cuti')
            ->assertSeeText('9 hari');
    }

    public function test_unrelated_employee_cannot_approve_leave(): void
    {
        $supervisor = $this->employeeWithUser();
        $applicant = $this->employeeWithUser(['supervisor_id' => $supervisor->id]);
        $replacement = $this->employeeWithUser();
        $unrelated = $this->employeeWithUser();
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $applicant->id,
            'replacement_employee_id' => $replacement->id,
            'direct_supervisor_id' => $supervisor->id,
        ]);

        $this->actingAs($unrelated->user)->put(route('leave-requests.supervisor-approval', $leaveRequest), [
            'decision' => 'approve',
        ])->assertForbidden();

        $this->assertSame(LeaveRequestStatus::PendingSupervisor, $leaveRequest->refresh()->status);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function employeeWithUser(array $attributes = [], string $role = 'staff'): Employee
    {
        $user = User::factory()->create(['role' => $role]);

        return Employee::factory()->active()->for($user)->create($attributes);
    }
}
