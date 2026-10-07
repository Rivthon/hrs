<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBusinessTripResponseRequest;
use App\Models\BusinessTrip;
use Illuminate\Http\RedirectResponse;

class BusinessTripResponseController extends Controller
{
    public function update(UpdateBusinessTripResponseRequest $request, BusinessTrip $businessTrip): RedirectResponse
    {
        $decision = $request->validated('decision');
        $businessTrip->update([
            'status' => $decision,
            'responded_at' => now(),
            'rejection_reason' => $decision === 'rejected' ? $request->validated('rejection_reason') : null,
        ]);

        return back()->with('success', $decision === 'accepted' ? 'Penugasan berhasil diterima.' : 'Penugasan telah ditolak.');
    }
}
