<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBusinessTripReportRequest;
use App\Models\BusinessTrip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class BusinessTripReportController extends Controller
{
    public function update(UpdateBusinessTripReportRequest $request, BusinessTrip $businessTrip): RedirectResponse
    {
        $reportData = $request->safe()->except('report_image');

        if ($request->hasFile('report_image')) {
            $newImagePath = $request->file('report_image')->store('business-trip-reports', 'public');

            if ($businessTrip->report_image_path) {
                Storage::disk('public')->delete($businessTrip->report_image_path);
            }

            $reportData['report_image_path'] = $newImagePath;
        }

        $businessTrip->update([
            ...$reportData,
            'status' => 'reported',
            'reported_at' => now(),
        ]);

        return back()->with('success', 'Laporan perjalanan dinas berhasil disimpan.');
    }
}
