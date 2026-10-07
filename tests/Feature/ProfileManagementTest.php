<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_employee_can_open_profile_from_sidebar(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        Employee::factory()->for($user)->create();

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()
            ->assertSeeText('Profil Saya')
            ->assertSeeText('Ganti Password');
    }

    public function test_employee_can_update_own_biodata_without_changing_protected_employment_data(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $employee = Employee::factory()->for($user)->create(['nip' => 'NIP-ASLI', 'base_salary' => 5000000]);

        $this->actingAs($user)->put(route('profile.update'), [
            ...$this->profilePayload(),
            'full_name' => 'Nama Profil Baru',
            'nip' => 'NIP-DIUBAH',
            'base_salary' => 1,
        ])->assertRedirect()->assertSessionHas('success');

        $employee->refresh();
        $this->assertSame('Nama Profil Baru', $employee->full_name);
        $this->assertSame('NIP-ASLI', $employee->nip);
        $this->assertSame('5000000.00', $employee->base_salary);
        $this->assertSame('Nama Profil Baru', $user->refresh()->name);
        $this->assertDatabaseHas('audit_logs', ['event' => 'profile.updated', 'actor_user_id' => $user->id]);
    }

    public function test_non_lecturer_can_update_email_and_password(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        Employee::factory()->for($user)->create();

        $this->actingAs($user)->put(route('profile.email.update'), [
            'email' => 'profil.baru@example.com',
            'current_password' => 'password',
        ])->assertRedirect()->assertSessionHas('success');
        $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'password',
            'password' => 'Baru12345',
            'password_confirmation' => 'Baru12345',
        ])->assertRedirect()->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('profil.baru@example.com', $user->email);
        $this->assertSame('profil.baru@example.com', $user->employee->email);
        $this->assertTrue(Hash::check('Baru12345', $user->password));
    }

    public function test_lecturer_password_is_synchronized_to_pas(): void
    {
        config()->set('database.connections.pas', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true]);
        DB::purge('pas');
        Schema::connection('pas')->create('dosen', function (Blueprint $table): void {
            $table->id('dosen_id');
            $table->string('password');
        });
        DB::connection('pas')->table('dosen')->insert(['dosen_id' => 77, 'password' => Hash::make('password')]);
        $user = User::factory()->create(['role' => 'dosen']);
        Employee::factory()->for($user)->create(['pas_dosen_id' => 77]);

        $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'password',
            'password' => 'DosenBaru123',
            'password_confirmation' => 'DosenBaru123',
        ])->assertRedirect()->assertSessionHas('success');

        $pasPassword = DB::connection('pas')->table('dosen')->where('dosen_id', 77)->value('password');
        $this->assertTrue(Hash::check('DosenBaru123', $pasPassword));
        $this->assertTrue(Hash::check('DosenBaru123', $user->refresh()->password));
    }

    /** @return array<string, mixed> */
    private function profilePayload(): array
    {
        return [
            'full_name' => 'Nama Pegawai',
            'title_prefix' => null,
            'title_suffix' => 'S.Kom.',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'phone' => '08123456789',
            'identity_address' => 'Alamat KTP',
            'residential_address' => 'Alamat Rumah',
            'last_education' => 'S1',
            'university' => 'Universitas Contoh',
            'study_program' => 'Sistem Informasi',
            'mother_name' => 'Nama Ibu',
        ];
    }
}
