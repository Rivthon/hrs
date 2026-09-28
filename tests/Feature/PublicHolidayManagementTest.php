<?php

namespace Tests\Feature;

use App\Models\PublicHoliday;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PublicHolidayManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_hr_can_add_public_holiday(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);

        $this->actingAs($hr)->post(route('public-holidays.store'), [
            'holiday_date' => '2026-12-25',
            'name' => 'Hari Raya Natal',
        ])->assertRedirect();

        $holiday = PublicHoliday::whereDate('holiday_date', '2026-12-25')->firstOrFail();
        $this->assertModelExists($holiday);
        $this->assertSame('Hari Raya Natal', $holiday->name);
    }

    public function test_staff_cannot_manage_public_holidays(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get(route('public-holidays.index'))->assertForbidden();
    }
}
