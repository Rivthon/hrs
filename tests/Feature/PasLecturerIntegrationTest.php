<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PasLecturerIntegrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.connections.pas', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        DB::purge('pas');

        Schema::connection('pas')->create('dosen', function (Blueprint $table): void {
            $table->id('dosen_id');
            $table->string('kd_dosen')->nullable();
            $table->string('nama');
            $table->string('nidn')->nullable();
            $table->string('email');
            $table->string('password')->nullable();
        });
    }

    public function test_lecturer_can_log_in_with_pas_nidn_and_password(): void
    {
        $user = User::factory()->create(['role' => 'dosen', 'email' => 'lama@example.com']);
        $employee = Employee::factory()->for($user)->create([
            'nidn' => '0417068401',
            'email' => 'lama@example.com',
        ]);
        DB::connection('pas')->table('dosen')->insert([
            'dosen_id' => 6,
            'kd_dosen' => 'DTB04336501001',
            'nama' => 'Mukhlisiana Ahmad',
            'nidn' => '0417068401',
            'email' => 'mukhlisiana@sbh.ac.id',
            'password' => Hash::make('password-pas'),
        ]);

        $this->post(route('login'), [
            'email' => '0417068401',
            'password' => 'password-pas',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(6, $employee->refresh()->pas_dosen_id);
        $this->assertSame('DTB04336501001', $employee->pas_kode_dosen);
        $this->assertSame('mukhlisiana@sbh.ac.id', $user->refresh()->email);
    }

    public function test_old_password_is_rejected_after_pas_password_is_reset(): void
    {
        $user = User::factory()->create(['role' => 'dosen', 'email' => 'dosen@sbh.ac.id']);
        Employee::factory()->for($user)->create([
            'nidn' => '0417068401',
            'email' => 'dosen@sbh.ac.id',
        ]);
        DB::connection('pas')->table('dosen')->insert([
            'dosen_id' => 6,
            'kd_dosen' => 'DTB001',
            'nama' => 'Dosen PAS',
            'nidn' => '0417068401',
            'email' => 'dosen@sbh.ac.id',
            'password' => Hash::make('password-lama'),
        ]);

        $this->post(route('login'), ['email' => 'dosen@sbh.ac.id', 'password' => 'password-lama'])
            ->assertRedirect(route('dashboard'));
        $this->post(route('logout'));

        DB::connection('pas')->table('dosen')->where('dosen_id', 6)->update([
            'password' => Hash::make('password-baru'),
        ]);

        $this->post(route('login'), ['email' => 'dosen@sbh.ac.id', 'password' => 'password-lama'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post(route('login'), ['email' => 'DTB001', 'password' => 'password-baru'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_non_lecturer_cannot_open_personal_bap(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get(route('lecturer-bap.index'))->assertForbidden();
    }
}
