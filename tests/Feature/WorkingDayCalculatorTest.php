<?php

namespace Tests\Feature;

use App\Models\PublicHoliday;
use App\Services\WorkingDayCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class WorkingDayCalculatorTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_excludes_weekends_and_public_holidays(): void
    {
        PublicHoliday::factory()->create(['holiday_date' => '2026-10-05']);

        $days = (new WorkingDayCalculator)->count(
            CarbonImmutable::parse('2026-10-02'),
            CarbonImmutable::parse('2026-10-06'),
        );

        $this->assertSame(2, $days);
    }
}
