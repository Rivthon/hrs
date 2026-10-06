<?php

namespace App\Console\Commands;

use App\Actions\SyncPasLecturersAction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('pas:sync-lecturers')]
#[Description('Sinkronkan data dosen PAS ke Manajemen User HRS')]
class SyncPasLecturers extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SyncPasLecturersAction $syncPasLecturers): int
    {
        $result = $syncPasLecturers->handle();

        $this->info("Sinkronisasi selesai: {$result['created']} dibuat, {$result['updated']} diperbarui.");

        return self::SUCCESS;
    }
}
