<?php

namespace App\Actions;

use App\Models\PublicHoliday;
use Illuminate\Support\Facades\Http;

class SyncIndonesianPublicHolidaysAction
{
    /**
     * @param  array<int, int>  $years
     */
    public function handle(array $years): int
    {
        $calendar = Http::connectTimeout(5)
            ->timeout(15)
            ->retry([200, 500, 1000])
            ->get(config('services.google_holidays.calendar_url'))
            ->throw()
            ->body();

        $holidays = $this->parseCalendar($calendar, $years);

        PublicHoliday::upsert($holidays, ['holiday_date'], ['name']);

        return count($holidays);
    }

    /**
     * @param  array<int, int>  $years
     * @return array<int, array{holiday_date: string, name: string}>
     */
    private function parseCalendar(string $calendar, array $years): array
    {
        $calendar = preg_replace("/\r?\n[ \t]/", '', $calendar) ?? $calendar;
        preg_match_all('/BEGIN:VEVENT(.*?)END:VEVENT/s', $calendar, $events);

        return collect($events[1])
            ->map(function (string $event): ?array {
                preg_match('/DTSTART(?:;VALUE=DATE)?:([0-9]{8})/', $event, $date);
                preg_match('/^SUMMARY:(.+)$/m', $event, $summary);
                preg_match('/^DESCRIPTION:(.+)$/m', $event, $description);

                if (! isset($date[1], $summary[1], $description[1]) || ! str_contains($description[1], 'Hari libur nasional')) {
                    return null;
                }

                return [
                    'holiday_date' => substr($date[1], 0, 4).'-'.substr($date[1], 4, 2).'-'.substr($date[1], 6, 2),
                    'name' => $this->decodeCalendarText($summary[1]),
                ];
            })
            ->filter(fn (?array $holiday): bool => $holiday !== null && in_array((int) substr($holiday['holiday_date'], 0, 4), $years, true))
            ->unique('holiday_date')
            ->values()
            ->all();
    }

    private function decodeCalendarText(string $value): string
    {
        return str_replace(['\\n', '\\,', '\\;', '\\\\'], ["\n", ',', ';', '\\'], trim($value));
    }
}
