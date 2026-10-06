<?php

namespace Tests\Feature;

use App\Models\PublicHoliday;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
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

    public function test_hr_can_sync_indonesian_national_holidays_for_selected_year(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            config('services.google_holidays.calendar_url') => Http::response($this->calendarFixture(), 200, [
                'Content-Type' => 'text/calendar',
            ]),
        ]);
        $hr = User::factory()->create(['role' => 'hr']);

        $this->actingAs($hr)->post(route('public-holidays.sync.store'), ['year' => 2026])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('public_holidays', [
            'holiday_date' => '2026-08-17',
            'name' => 'Hari Proklamasi Kemerdekaan R.I.',
        ]);
        $this->assertDatabaseMissing('public_holidays', ['holiday_date' => '2026-12-31']);
        $this->assertDatabaseMissing('public_holidays', ['holiday_date' => '2027-01-01']);
        Http::assertSentCount(1);
    }

    public function test_staff_cannot_sync_indonesian_holidays(): void
    {
        Http::preventStrayRequests();
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->post(route('public-holidays.sync.store'), ['year' => 2026])
            ->assertForbidden();

        Http::assertNothingSent();
    }

    private function calendarFixture(): string
    {
        return <<<'ICS'
BEGIN:VCALENDAR
BEGIN:VEVENT
DTSTART;VALUE=DATE:20260817
SUMMARY:Hari Proklamasi Kemerdekaan R.I.
DESCRIPTION:Hari libur nasional
END:VEVENT
BEGIN:VEVENT
DTSTART;VALUE=DATE:20261231
SUMMARY:Malam Tahun Baru
DESCRIPTION:Perayaan\nUntuk menyembunyikan kalender perayaan
END:VEVENT
BEGIN:VEVENT
DTSTART;VALUE=DATE:20270101
SUMMARY:Hari Tahun Baru
DESCRIPTION:Hari libur nasional
END:VEVENT
END:VCALENDAR
ICS;
    }
}
