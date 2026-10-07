<?php

namespace Tests\Feature;

use App\Models\BusinessTrip;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessTripWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_business_trip_to_employee(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = Employee::factory()->active()->create(['full_name' => 'Pegawai Tujuan']);

        $this->actingAs($admin)->post(route('business-trips.store'), [
            'employee_id' => $employee->id,
            'title' => 'Rapat Koordinasi Nasional',
            'destination' => 'Jakarta',
            'purpose' => 'Menghadiri rapat koordinasi program kerja.',
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-22',
            'transportation' => 'Kereta',
            'allowance' => 1500000,
            'assignment_notes' => 'Membawa surat tugas.',
        ])->assertRedirect();

        $this->assertDatabaseHas('business_trips', [
            'employee_id' => $employee->id,
            'assigned_by_user_id' => $admin->id,
            'title' => 'Rapat Koordinasi Nasional',
            'status' => 'assigned',
        ]);

        $this->actingAs($admin)->get(route('business-trips.index'))
            ->assertOk()
            ->assertSeeText('Rapat Koordinasi Nasional')
            ->assertSeeText('Pegawai Tujuan');
    }

    public function test_employee_sees_new_assignment_and_can_accept_it(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $employee = Employee::factory()->for($user)->create();
        $businessTrip = BusinessTrip::factory()->for($employee)->create(['title' => 'Kunjungan Institusi']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('penugasan perjalanan dinas baru')
            ->assertSeeText('Kunjungan Institusi');

        $this->actingAs($user)->get(route('business-trips.index'))
            ->assertOk()
            ->assertSeeText('Terima Tugas')
            ->assertSeeText('Laporan Perjalanan Dinas');

        $this->actingAs($user)->put(route('business-trips.response.update', $businessTrip), [
            'decision' => 'accepted',
        ])->assertRedirect();

        $this->assertSame('accepted', $businessTrip->refresh()->status);
        $this->assertNotNull($businessTrip->responded_at);
    }

    public function test_employee_must_provide_reason_when_rejecting_assignment(): void
    {
        $user = User::factory()->create(['role' => 'dosen']);
        $employee = Employee::factory()->for($user)->create();
        $businessTrip = BusinessTrip::factory()->for($employee)->create();

        $this->actingAs($user)->put(route('business-trips.response.update', $businessTrip), [
            'decision' => 'rejected',
        ])->assertSessionHasErrors('rejection_reason');

        $this->assertSame('assigned', $businessTrip->refresh()->status);
    }

    public function test_employee_cannot_respond_to_another_employees_assignment(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        Employee::factory()->for($user)->create();
        $businessTrip = BusinessTrip::factory()->create();

        $this->actingAs($user)->put(route('business-trips.response.update', $businessTrip), [
            'decision' => 'accepted',
        ])->assertForbidden();

        $this->assertSame('assigned', $businessTrip->refresh()->status);
    }

    public function test_employee_can_submit_report_after_accepting_assignment(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'dosen']);
        $employee = Employee::factory()->for($user)->create();
        $businessTrip = BusinessTrip::factory()->for($employee)->create(['status' => 'accepted']);

        $this->actingAs($user)->put(route('business-trips.report.update', $businessTrip), [
            'report_summary' => 'Mengikuti seluruh rangkaian rapat koordinasi.',
            'report_result' => 'Kesepakatan kerja sama berhasil diperoleh.',
            'report_notes' => 'Dokumen tindak lanjut akan dikirim.',
            'report_image' => UploadedFile::fake()->image('dokumentasi.jpg', 1200, 800),
        ])->assertRedirect();

        $businessTrip->refresh();
        $this->assertSame('reported', $businessTrip->status);
        $this->assertSame('Kesepakatan kerja sama berhasil diperoleh.', $businessTrip->report_result);
        $this->assertNotNull($businessTrip->reported_at);
        Storage::disk('public')->assertExists($businessTrip->report_image_path);
    }

    public function test_employee_report_rejects_non_image_documentation(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'staff']);
        $employee = Employee::factory()->for($user)->create();
        $businessTrip = BusinessTrip::factory()->for($employee)->create(['status' => 'accepted']);

        $this->actingAs($user)->put(route('business-trips.report.update', $businessTrip), [
            'report_summary' => 'Ringkasan kegiatan.',
            'report_result' => 'Hasil kegiatan.',
            'report_image' => UploadedFile::fake()->create('laporan.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('report_image');

        $this->assertNull($businessTrip->refresh()->reported_at);
        Storage::disk('public')->assertDirectoryEmpty('business-trip-reports');
    }

    public function test_employee_cannot_report_unaccepted_or_another_employees_assignment(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $employee = Employee::factory()->for($user)->create();
        $unacceptedTrip = BusinessTrip::factory()->for($employee)->create(['status' => 'assigned']);
        $otherTrip = BusinessTrip::factory()->create(['status' => 'accepted']);
        $payload = ['report_summary' => 'Ringkasan', 'report_result' => 'Hasil'];

        $this->actingAs($user)->put(route('business-trips.report.update', $unacceptedTrip), $payload)->assertForbidden();
        $this->actingAs($user)->put(route('business-trips.report.update', $otherTrip), $payload)->assertForbidden();

        $this->assertNull($unacceptedTrip->refresh()->reported_at);
        $this->assertNull($otherTrip->refresh()->reported_at);
    }

    public function test_staff_cannot_create_assignment_and_admin_can_read_submitted_report(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        Employee::factory()->for($staff)->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $businessTrip = BusinessTrip::factory()->create([
            'status' => 'reported',
            'report_summary' => 'Laporan kegiatan rahasia unit.',
            'report_result' => 'Target kegiatan tercapai.',
            'reported_at' => now(),
        ]);

        $this->actingAs($staff)->post(route('business-trips.store'), [])->assertForbidden();

        $this->actingAs($admin)->get(route('business-trips.show', $businessTrip))
            ->assertOk()
            ->assertSeeText('Laporan kegiatan rahasia unit.')
            ->assertSeeText('Target kegiatan tercapai.');
    }

    public function test_direct_supervisor_can_open_subordinate_report_but_unrelated_employee_cannot(): void
    {
        $supervisorUser = User::factory()->create(['role' => 'staff']);
        $supervisor = Employee::factory()->for($supervisorUser)->create();
        $subordinate = Employee::factory()->create(['supervisor_id' => $supervisor->id, 'full_name' => 'Bawahan Pelapor']);
        $businessTrip = BusinessTrip::factory()->for($subordinate)->create([
            'status' => 'reported',
            'report_summary' => 'Mengikuti kegiatan koordinasi.',
            'report_result' => 'Kerja sama berhasil disepakati.',
            'reported_at' => now(),
        ]);
        $unrelatedUser = User::factory()->create(['role' => 'staff']);
        Employee::factory()->for($unrelatedUser)->create();

        $this->actingAs($supervisorUser)->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Laporan Perjalanan Dinas Bawahan')
            ->assertSeeText('Bawahan Pelapor');
        $this->actingAs($supervisorUser)->get(route('business-trips.show', $businessTrip))
            ->assertOk()
            ->assertSeeText('Mengikuti kegiatan koordinasi.')
            ->assertSeeText('Kerja sama berhasil disepakati.');
        $this->actingAs($unrelatedUser)->get(route('business-trips.show', $businessTrip))->assertForbidden();
    }

    public function test_business_trip_timestamps_are_displayed_in_wib(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $employee = Employee::factory()->for($user)->create();
        $businessTrip = BusinessTrip::factory()->for($employee)->create([
            'status' => 'reported',
            'responded_at' => '2026-10-07 03:34:00',
            'reported_at' => '2026-10-07 03:42:00',
            'report_summary' => 'Ringkasan kegiatan.',
            'report_result' => 'Hasil kegiatan.',
        ]);

        $this->actingAs($user)->get(route('business-trips.show', $businessTrip))
            ->assertOk()
            ->assertSeeText('Laporan Selesai pada 07/10/2026 10:34 WIB')
            ->assertSeeText('07/10/2026 10:42 WIB');
    }
}
