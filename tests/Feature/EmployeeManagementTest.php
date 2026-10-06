<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_from_user_management(): void
    {
        $this->get(route('employees.index'))->assertRedirect(route('login'));
    }

    public function test_staff_role_is_forbidden_from_user_management(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get(route('employees.index'))->assertForbidden();
    }

    public function test_admin_can_create_user_with_nip_as_hashed_initial_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $department = Department::factory()->create();
        $position = Position::factory()->create();

        $response = $this->actingAs($admin)->post(route('employees.store'), $this->validPayload($department, $position));

        $employee = Employee::where('nip', '198765432100000001')->firstOrFail();
        $response->assertRedirect(route('employees.show', $employee));
        $this->assertSame('Jl. Kampus No. 10', $employee->residential_address);
        $this->assertTrue($employee->user->must_change_password);
        $this->assertTrue(Hash::check('198765432100000001', $employee->user->password));
    }

    public function test_admin_editing_employee_without_user_creates_account_with_nip_as_initial_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $department = Department::factory()->create();
        $position = Position::factory()->create();
        $employee = Employee::factory()
            ->for($department)
            ->for($position)
            ->create([
                'user_id' => null,
                'nip' => '198765432100000001',
                'email' => 'email-lama@kampus.ac.id',
            ]);

        $response = $this->actingAs($admin)->put(
            route('employees.update', $employee),
            $this->validPayload($department, $position, [
                'full_name' => 'Mochamad Rival Maurizky',
                'nip' => $employee->nip,
                'email' => 'rival@sbh.ac.id',
            ]),
        );

        $response->assertRedirect(route('employees.show', $employee));

        $employee->refresh()->load('user');

        $this->assertNotNull($employee->user);
        $this->assertSame('rival@sbh.ac.id', $employee->user->email);
        $this->assertTrue($employee->user->must_change_password);
        $this->assertTrue(Hash::check($employee->nip, $employee->user->password));
    }

    public function test_lecturer_must_have_nidn(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $department = Department::factory()->create();
        $position = Position::factory()->create();
        $payload = $this->validPayload($department, $position, ['role' => 'dosen', 'nidn' => null]);

        $this->actingAs($admin)->post(route('employees.store'), $payload)
            ->assertSessionHasErrors('nidn');

        $this->assertDatabaseMissing('employees', ['nip' => $payload['nip']]);
    }

    public function test_detail_displays_automatically_calculated_length_of_service_and_escapes_name(): void
    {
        $this->travelTo('2026-09-28');
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = Employee::factory()->create([
            'full_name' => '<script>alert(1)</script>',
            'joined_on' => '2020-07-25',
        ]);

        $this->actingAs($admin)->get(route('employees.show', $employee))
            ->assertSee('6 tahun, 2 bulan, 3 hari')
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(Department $department, Position $position, array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Budi Santoso',
            'title_prefix' => null,
            'title_suffix' => 'S.Kom.',
            'gender' => 'male',
            'joined_on' => '2024-01-02',
            'date_of_birth' => '1990-05-10',
            'nik' => '3273010101900001',
            'npwp' => null,
            'bpjs_health_number' => 'BPJSK001',
            'bpjs_employment_number' => 'BPJSTK001',
            'nidn' => null,
            'nip' => '198765432100000001',
            'department_id' => $department->id,
            'position_id' => $position->id,
            'phone' => '081234567890',
            'identity_address' => 'Jl. Kampus No. 10',
            'same_as_identity_address' => '1',
            'residential_address' => null,
            'last_education' => 'S1',
            'university' => 'Universitas Contoh',
            'study_program' => 'Sistem Informasi',
            'email' => 'budi@kampus.ac.id',
            'annual_leave_days' => 12,
            'supervisor_id' => null,
            'mother_name' => 'Siti Aminah',
            'base_salary' => 5000000,
            'transport_allowance' => 500000,
            'role' => 'staff',
            'employment_type' => 'permanent',
            'status' => 'active',
        ], $overrides);
    }
}
