<?php

namespace App\Actions;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CalculatePasTeachingHonorAction
{
    /**
     * @param  array<int, int>  $pasLecturerIds
     * @return array<int, int>
     */
    public function handle(array $pasLecturerIds, Carbon $periodDate): array
    {
        if ($pasLecturerIds === []) {
            return [];
        }

        $startDate = $periodDate->copy()->startOfMonth()->toDateString();
        $counts = collect($pasLecturerIds)->mapWithKeys(fn (int $id): array => [$id => 0]);
        $today = now();

        if ($periodDate->copy()->startOfMonth()->isAfter($today)) {
            return $counts->all();
        }

        $endDate = $periodDate->isSameMonth($today)
            ? $today->toDateString()
            : $periodDate->copy()->endOfMonth()->toDateString();

        foreach (['pertemuan', 'pertemuan_praktik'] as $table) {
            DB::connection('pas')->table($table)
                ->whereIn('dosen_id', $pasLecturerIds)
                ->whereBetween('tanggal_pertemuan', [$startDate, $endDate])
                ->selectRaw('dosen_id, COUNT(*) as total')
                ->groupBy('dosen_id')
                ->get()
                ->each(function ($row) use ($counts): void {
                    $counts[(int) $row->dosen_id] = $counts->get((int) $row->dosen_id, 0) + (int) $row->total;
                });
        }

        $rate = (int) config('payroll.teaching_honor_per_meeting', 50000);

        return $counts->map(fn (int $meetingCount): int => $meetingCount * $rate)->all();
    }
}
