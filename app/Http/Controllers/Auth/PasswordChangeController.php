<?php

namespace App\Http\Controllers\Auth;

use App\Actions\RecordAuditLogAction;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class PasswordChangeController extends Controller
{
    public function edit(Request $request): View
    {
        abort_if($request->user()->role === 'dosen', 403);

        return view('auth.change-password');
    }

    public function update(Request $request, RecordAuditLogAction $recordAuditLog): RedirectResponse
    {
        abort_if($request->user()->role === 'dosen', 403);

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()],
        ]);

        $user = $request->user();
        $user->update(['password' => $validated['password'], 'must_change_password' => false]);
        $request->session()->regenerate();
        $recordAuditLog->handle('password.changed', $user, 'Pengguna mengganti password awal');

        return redirect()->route('dashboard')->with('success', 'Password berhasil diganti.');
    }
}
