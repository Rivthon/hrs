<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_sees_public_landing_page_with_login_link(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('Kelola SDM kampus dalam satu sistem')
            ->assertSee(route('login'));
    }

    public function test_guest_cannot_open_dashboard_directly(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_open_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Ringkasan SDM');
    }
}
