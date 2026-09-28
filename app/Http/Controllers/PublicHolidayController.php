<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicHolidayRequest;
use App\Models\PublicHoliday;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PublicHolidayController extends Controller
{
    public function index(): View
    {
        $holidays = PublicHoliday::orderBy('holiday_date')->paginate(20);

        return view('public-holidays.index', compact('holidays'));
    }

    public function store(StorePublicHolidayRequest $request): RedirectResponse
    {
        PublicHoliday::create($request->validated());

        return back()->with('success', 'Tanggal merah berhasil ditambahkan.');
    }

    public function destroy(PublicHoliday $publicHoliday): RedirectResponse
    {
        $publicHoliday->delete();

        return back()->with('success', 'Tanggal merah berhasil dihapus.');
    }
}
