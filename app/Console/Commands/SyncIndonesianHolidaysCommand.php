<?php

namespace App\Console\Commands;

use App\Actions\SyncIndonesianPublicHolidaysAction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('holidays:sync-indonesia {--year=* : Tahun yang akan disinkronkan}')]
#[Description('Sinkronkan tanggal merah dari kalender Indonesia')]
class SyncIndonesianHolidaysCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SyncIndonesianPublicHolidaysAction $syncHolidays): int
    {
        $years = collect($this->option('year'))
            ->whenEmpty(fn ($collection) => $collection->push(now()->year, now()->addYear()->year))
            ->map(fn (string|int $year): int => (int) $year)
            ->unique()
            ->values()
            ->all();

        try {
            $count = $syncHolidays->handle($years);
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Sinkronisasi kalender Indonesia gagal: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info("{$count} tanggal merah berhasil disinkronkan.");

        return self::SUCCESS;
    }
}
