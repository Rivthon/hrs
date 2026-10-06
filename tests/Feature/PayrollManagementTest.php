<?php

namespace Tests\Feature;

use App\Actions\GeneratePayrollSlipPdfAction;
use App\Jobs\SendPayrollSlipEmail;
use App\Mail\PayrollSlipMail;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PayrollManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_hr_can_generate_period_from_active_employee_salary(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $employee = Employee::factory()->active()->create([
            'base_salary' => 6000000,
            'transport_allowance' => 750000,
        ]);
        Employee::factory()->create(['status' => 'inactive']);

        $response = $this->actingAs($hr)->post(route('payroll-periods.store'), ['period' => '2026-09']);

        $period = PayrollPeriod::firstOrFail();
        $payroll = Payroll::whereBelongsTo($employee)->firstOrFail();
        $response->assertRedirect(route('payroll-periods.show', $period));
        $this->assertSame('6000000.00', $payroll->base_salary);
        $this->assertSame('750000.00', $payroll->transport_allowance);
        $this->assertSame('6750000.00', $payroll->net_salary);
        $this->assertDatabaseCount('payrolls', 1);
    }

    public function test_hr_can_update_manual_components_and_totals_are_recalculated(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $employeeUser = User::factory()->create(['role' => 'dosen']);
        $employee = Employee::factory()->active()->for($employeeUser)->create();
        $payroll = Payroll::factory()->for($employee)->create();
        $payload = $this->payrollPayload([
            'position_allowance' => 1000000,
            'teaching_honor' => 1500000,
            'bpjs_health_deduction' => 150000,
            'income_tax_deduction' => 250000,
        ]);

        $this->actingAs($hr)->put(route('payrolls.update', $payroll), $payload)
            ->assertRedirect(route('payrolls.show', $payroll));

        $payroll->refresh();
        $this->assertSame('8000000.00', $payroll->gross_income);
        $this->assertSame('400000.00', $payroll->total_deductions);
        $this->assertSame('7600000.00', $payroll->net_salary);
    }

    public function test_hr_can_submit_indonesian_rupiah_formatted_amounts(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $employeeUser = User::factory()->create(['role' => 'dosen']);
        $payroll = Payroll::factory()->for(Employee::factory()->for($employeeUser))->create();
        $payload = $this->payrollPayload([
            'position_allowance' => 'Rp 1.250.000',
            'teaching_honor' => 'Rp 750.000',
            'bpjs_health_deduction' => 'Rp 150.000',
        ]);

        $this->actingAs($hr)->put(route('payrolls.update', $payroll), $payload)
            ->assertRedirect(route('payrolls.show', $payroll));

        $payroll->refresh();
        $this->assertSame('1250000.00', $payroll->position_allowance);
        $this->assertSame('750000.00', $payroll->teaching_honor);
        $this->assertSame('150000.00', $payroll->bpjs_health_deduction);
    }

    public function test_lecturer_honor_is_rejected_for_non_lecturer(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $staffUser = User::factory()->create(['role' => 'staff']);
        $payroll = Payroll::factory()->for(Employee::factory()->for($staffUser))->create();

        $this->actingAs($hr)->put(route('payrolls.update', $payroll), $this->payrollPayload(['teaching_honor' => 500000]))
            ->assertSessionHasErrors('teaching_honor');

        $this->assertSame('0.00', $payroll->refresh()->teaching_honor);
    }

    public function test_lecturer_payroll_form_only_shows_lecturer_specific_earnings(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $payroll = Payroll::factory()->for(Employee::factory()->for($lecturer))->create();

        $this->actingAs($hr)->get(route('payrolls.edit', $payroll))
            ->assertOk()
            ->assertSeeText('Honor Mengajar')
            ->assertSeeText('Honor Pembimbing Skripsi')
            ->assertDontSeeText('Honor Mengawas');
    }

    public function test_staff_payroll_form_only_shows_tendik_specific_earnings(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $staff = User::factory()->create(['role' => 'staff']);
        $payroll = Payroll::factory()->for(Employee::factory()->for($staff))->create();

        $this->actingAs($hr)->get(route('payrolls.edit', $payroll))
            ->assertOk()
            ->assertSeeText('Honor Mengawas')
            ->assertDontSeeText('Honor Mengajar')
            ->assertDontSeeText('Honor Pembimbing Skripsi');
    }

    public function test_staff_cannot_access_payroll_management(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get(route('payroll-periods.index'))->assertForbidden();
    }

    public function test_hr_can_delete_period_and_all_of_its_payroll_details(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $period = PayrollPeriod::factory()->create();
        $payroll = Payroll::factory()->for($period, 'period')->create();

        $this->actingAs($hr)->delete(route('payroll-periods.destroy', $period))
            ->assertRedirect(route('payroll-periods.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($period);
        $this->assertModelMissing($payroll);
    }

    public function test_staff_cannot_delete_payroll_period(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $period = PayrollPeriod::factory()->create();

        $this->actingAs($staff)->delete(route('payroll-periods.destroy', $period))
            ->assertForbidden();

        $this->assertModelExists($period);
    }

    public function test_hr_can_download_payroll_slip_as_pdf(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $payroll = Payroll::factory()->create();

        $response = $this->actingAs($hr)->get(route('payrolls.slip', $payroll));

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_payroll_slip_uses_requested_logo_and_verified_stamp(): void
    {
        $payroll = Payroll::factory()->create();
        $payroll->load(['employee.position', 'employee.user', 'period']);

        $this->view('payrolls.slip', [
            'payroll' => $payroll,
            'institution' => config('payroll.institution'),
            'signatory' => config('payroll.signatory'),
        ])->assertSee('12.png')
            ->assertSeeText('Biro Adm Umum & Keu')
            ->assertSeeText('STIKes Bogor Husada')
            ->assertSeeText('TERVERIFIKASI')
            ->assertSeeText('Lisnawati, S.E')
            ->assertDontSee('payroll-signature.png');
    }

    public function test_hr_can_queue_one_payroll_slip_email(): void
    {
        Queue::fake([SendPayrollSlipEmail::class]);
        $hr = User::factory()->create(['role' => 'hr']);
        $payroll = Payroll::factory()->for(Employee::factory()->create(['email' => 'pegawai@example.com']))->create();

        $this->actingAs($hr)->post(route('payrolls.email-slip.store', $payroll))
            ->assertRedirect()
            ->assertSessionHas('success');

        Queue::assertPushed(SendPayrollSlipEmail::class, fn (SendPayrollSlipEmail $job): bool => $job->payroll->is($payroll));
    }

    public function test_hr_can_queue_all_slips_with_registered_email(): void
    {
        Queue::fake([SendPayrollSlipEmail::class]);
        $hr = User::factory()->create(['role' => 'hr']);
        $period = PayrollPeriod::factory()->create();
        Payroll::factory()->for($period, 'period')->for(Employee::factory()->create(['email' => 'satu@example.com']))->create();
        Payroll::factory()->for($period, 'period')->for(Employee::factory()->create(['email' => 'dua@example.com']))->create();
        Payroll::factory()->for($period, 'period')->for(Employee::factory()->create(['email' => '']))->create();

        $this->actingAs($hr)->post(route('payroll-periods.email-slips.store', $period))
            ->assertRedirect()
            ->assertSessionHas('success');

        Queue::assertPushed(SendPayrollSlipEmail::class, 2);
        $this->assertSame('finalized', $period->refresh()->status);
        $this->assertNotNull($period->finalized_at);
    }

    public function test_queued_payroll_job_sends_pdf_to_employee_email(): void
    {
        Mail::fake();
        $payroll = Payroll::factory()->for(Employee::factory()->create(['email' => 'pegawai@example.com']))->create();

        (new SendPayrollSlipEmail($payroll))->handle(new GeneratePayrollSlipPdfAction);

        Mail::assertSent(PayrollSlipMail::class, fn (PayrollSlipMail $mail): bool => $mail->hasTo('pegawai@example.com'));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payrollPayload(array $overrides = []): array
    {
        return array_merge([
            'position_allowance' => 0,
            'functional_allowance' => 0,
            'teaching_honor' => 0,
            'proctoring_honor' => 0,
            'final_seminar_honor' => 0,
            'thesis_defense_honor' => 0,
            'thesis_supervisor_honor' => 0,
            'pkk_supervision_honor' => 0,
            'practical_exam_honor' => 0,
            'duty_honor' => 0,
            'bpjs_employment_deduction' => 0,
            'bpjs_health_deduction' => 0,
            'income_tax_deduction' => 0,
            'transport_deduction' => 0,
            'lateness_deduction' => 0,
            'notes' => null,
        ], $overrides);
    }
}
