<?php

use App\Console\Commands\SyncIndonesianHolidaysCommand;
use App\Console\Commands\SyncPasLecturers;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(SyncIndonesianHolidaysCommand::class)
    ->monthlyOn(1, '02:00')
    ->withoutOverlapping(30);

Schedule::command(SyncPasLecturers::class)
    ->hourly()
    ->withoutOverlapping(30);
