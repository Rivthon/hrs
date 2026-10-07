<?php

namespace Tests\Feature;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveType;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PublicHoliday;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            'leave_type' => LeaveType::Annual->value,
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
            'leave_type' => LeaveType::Annual->value,
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

    public function test_sick_leave_requires_and_privately_stores_doctor_letter(): void
    {
        Storage::fake('local');
        $this->travelTo('2026-10-07');
        $supervisor = $this->employeeWithUser();
        $applicant = $this->employeeWithUser(['supervisor_id' => $supervisor->id]);
        $replacement = $this->employeeWithUser();
        $payload = [
            'leave_type' => LeaveType::Sick->value,
            'replacement_employee_id' => $replacement->id,
            'start_date' => '2026-10-12',
            'end_date' => '2026-10-16',
            'reason' => 'Memerlukan perawatan dan istirahat berdasarkan pemeriksaan dokter.',
        ];

        $this->actingAs($applicant->user)->post(route('leave-requests.store'), $payload)
            ->assertSessionHasErrors('supporting_document');

        $this->actingAs($applicant->user)->post(route('leave-requests.store'), [
            ...$payload,
            'supporting_document' => UploadedFile::fake()->image('surat-dokter.jpg'),
        ])->assertSessionHasNoErrors();

        $leaveRequest = LeaveRequest::query()->sole();
        $this->assertSame(LeaveType::Sick, $leaveRequest->leave_type);
        $this->assertSame(5, $leaveRequest->total_working_days);
        Storage::disk('local')->assertExists($leaveRequest->supporting_document_path);

        $this->actingAs($applicant->user)->get(route('leave-requests.document', $leaveRequest))->assertOk();
        $this->actingAs($replacement->user)->get(route('leave-requests.document', $leaveRequest))->assertForbidden();
    }

    public function test_half_day_leave_records_four_hour_duration_without_using_annual_balance(): void
    {
        $this->travelTo('2026-10-07');
        $supervisor = $this->employeeWithUser();
        $applicant = $this->employeeWithUser(['supervisor_id' => $supervisor->id]);
        $replacement = $this->employeeWithUser();

        $this->actingAs($applicant->user)->post(route('leave-requests.store'), [
            'leave_type' => LeaveType::HalfDay->value,
            'replacement_employee_id' => $replacement->id,
            'start_date' => '2026-10-12',
            'end_date' => '2026-10-12',
            'start_time' => '08:00',
            'end_time' => '12:00',
            'reason' => 'Memerlukan izin setengah hari untuk keperluan keluarga.',
        ])->assertSessionHasNoErrors();

        $leaveRequest = LeaveRequest::query()->sole();
        $this->assertSame(0, $leaveRequest->total_working_days);
        $this->assertSame(240, $leaveRequest->duration_minutes);
        $this->assertSame('4 jam', $leaveRequest->durationLabel());
    }

    public function test_maternity_leave_is_rejected_for_male_employee(): void
    {
        $this->travelTo('2026-10-07');
        $supervisor = $this->employeeWithUser();
        $applicant = $this->employeeWithUser(['supervisor_id' => $supervisor->id, 'gender' => 'male']);
        $replacement = $this->employeeWithUser();

        $this->actingAs($applicant->user)->post(route('leave-requests.store'), [
            'leave_type' => LeaveType::Maternity->value,
            'replacement_employee_id' => $replacement->id,
            'start_date' => '2026-10-12',
            'end_date' => '2026-12-31',
            'reason' => 'Pengajuan cuti melahirkan sesuai kebutuhan pegawai.',
        ])->assertSessionHasErrors('leave_type');

        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_female_employee_can_submit_maternity_leave_longer_than_three_days(): void
    {
        $this->travelTo('2026-10-07');
        $supervisor = $this->employeeWithUser();
        $applicant = $this->employeeWithUser(['supervisor_id' => $supervisor->id, 'gender' => 'female']);
        $replacement = $this->employeeWithUser();

        $this->actingAs($applicant->user)->post(route('leave-requests.store'), [
            'leave_type' => LeaveType::Maternity->value,
            'replacement_employee_id' => $replacement->id,
            'start_date' => '2026-10-12',
            'end_date' => '2026-10-30',
            'reason' => 'Pengajuan cuti melahirkan sesuai kebutuhan pegawai.',
        ])->assertSessionHasNoErrors();

        $leaveRequest = LeaveRequest::query()->sole();
        $this->assertSame(LeaveType::Maternity, $leaveRequest->leave_type);
        $this->assertSame(15, $leaveRequest->total_working_days);
    }

    public function test_hourly_leave_is_limited_to_four_hours(): void
    {
        $this->travelTo('2026-10-07');
        $supervisor = $this->employeeWithUser();
        $applicant = $this->employeeWithUser(['supervisor_id' => $supervisor->id]);
        $replacement = $this->employeeWithUser();
        $payload = [
            'leave_type' => LeaveType::Hourly->value,
            'replacement_employee_id' => $replacement->id,
            'start_date' => '2026-10-12',
            'end_date' => '2026-10-12',
            'start_time' => '08:00',
            'end_time' => '13:00',
            'reason' => 'Memerlukan izin per jam untuk keperluan administrasi keluarga.',
        ];

        $this->actingAs($applicant->user)->post(route('leave-requests.store'), $payload)
            ->assertSessionHasErrors('end_time');

        $this->actingAs($applicant->user)->post(route('leave-requests.store'), [
            ...$payload,
            'end_time' => '10:30',
        ])->assertSessionHasNoErrors();

        $leaveRequest = LeaveRequest::query()->sole();
        $this->assertSame(150, $leaveRequest->duration_minutes);
        $this->assertSame('2 jam 30 menit', $leaveRequest->durationLabel());
    }

    public function test_approved_non_annual_leave_does_not_reduce_annual_leave_balance(): void
    {
        $applicant = $this->employeeWithUser(['annual_leave_days' => 12]);
        LeaveRequest::factory()->for($applicant)->create([
            'leave_type' => LeaveType::Sick,
            'status' => LeaveRequestStatus::Approved,
            'total_working_days' => 5,
            'start_date' => now()->startOfYear()->addWeek(),
        ]);

        $this->actingAs($applicant->user)->get(route('leave-requests.index'))
            ->assertOk()
            ->assertSeeText('Sisa Cuti')
            ->assertSeeText('12 hari');
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
