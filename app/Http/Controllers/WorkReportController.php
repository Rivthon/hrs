<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkReportRequest;
use App\Models\WorkReport;
use Illuminate\Http\RedirectResponse;

class WorkReportController extends Controller
{
    public function store(StoreWorkReportRequest $request): RedirectResponse
    {
        $request->user()->employee->workReports()->create($request->validated());

        return back()->with('success', 'Laporan pekerjaan berhasil disimpan.');
    }

    public function destroy(WorkReport $workReport): RedirectResponse
    {
        abort_unless($workReport->employee_id === auth()->user()->employee?->id, 403);
        $workReport->delete();

        return back()->with('success', 'Laporan pekerjaan berhasil dihapus.');
    }
}
