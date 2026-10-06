<?php

namespace App\Http\Controllers;

use App\Actions\SyncIndonesianPublicHolidaysAction;
use App\Http\Requests\SyncPublicHolidaysRequest;
use Illuminate\Http\RedirectResponse;
use Throwable;

class PublicHolidaySyncController extends Controller
{
    public function store(SyncPublicHolidaysRequest $request, SyncIndonesianPublicHolidaysAction $syncHolidays): RedirectResponse
    {
        $year = $request->integer('year');

        try {
            $count = $syncHolidays->handle([$year]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Kalender Indonesia belum dapat dihubungi. Silakan coba kembali nanti.');
        }

        return back()->with('success', "{$count} tanggal merah Indonesia tahun {$year} berhasil disinkronkan.");
    }
}
