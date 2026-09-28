<?php

namespace App\Services;

use App\Models\PublicHoliday;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;

class WorkingDayCalculator
{
    public function count(CarbonInterface $startDate, CarbonInterface $endDate): int
    {
        $holidays = PublicHoliday::query()
            ->whereBetween('holiday_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('holiday_date')
            ->map(fn ($date): string => Carbon::parse($date)->toDateString())
            ->all();

        return collect(CarbonPeriod::create($startDate, $endDate))
            ->reject(fn (CarbonInterface $date): bool => $date->isWeekend() || in_array($date->toDateString(), $holidays, true))
            ->count();
    }
}
