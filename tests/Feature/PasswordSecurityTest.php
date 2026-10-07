<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_lecturer_with_temporary_password_must_change_it_before_accessing_app(): void
    {
        $user = User::factory()->create(['role' => 'staff', 'must_change_password' => true]);

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('password.change'));
    }

    public function test_non_lecturer_can_change_temporary_password(): void
    {
        $user = User::factory()->create(['role' => 'staff', 'must_change_password' => true]);

        $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'password',
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ])->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('PasswordBaru123', $user->password));
        $this->assertDatabaseHas('audit_logs', ['event' => 'password.changed', 'actor_user_id' => $user->id]);
    }

    public function test_hr_can_reset_non_lecturer_password_but_not_lecturer_password(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $staff = User::factory()->create(['role' => 'staff']);
        $staffEmployee = Employee::factory()->for($staff)->create();
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $lecturerEmployee = Employee::factory()->for($lecturer)->create();

        $response = $this->actingAs($hr)->post(route('employees.reset-password', $staffEmployee));

        $response->assertRedirect()->assertSessionHas('temporary_password');
        $this->assertTrue($staff->refresh()->must_change_password);
        $this->assertDatabaseHas('audit_logs', ['event' => 'password.reset', 'actor_user_id' => $hr->id]);
        $this->actingAs($hr)->post(route('employees.reset-password', $lecturerEmployee))->assertForbidden();
    }
}
