<?php

namespace App\Http\Controllers;

use App\Actions\RecordAuditLogAction;
use App\Http\Requests\UpdateProfilePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProfilePasswordController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateProfilePasswordRequest $request, RecordAuditLogAction $recordAuditLog): RedirectResponse
    {
        $user = $request->user();
        $password = $request->validated('password');

        if ($user->role === 'dosen') {
            try {
                $pasLecturer = DB::connection('pas')->table('dosen')->where('dosen_id', $user->employee->pas_dosen_id);
                if (! $pasLecturer->exists()) {
                    throw ValidationException::withMessages(['password' => 'Data dosen tidak ditemukan di PAS.']);
                }
                $pasLecturer->update(['password' => Hash::make($password)]);
            } catch (ValidationException $exception) {
                throw $exception;
            } catch (Throwable $exception) {
                report($exception);
                throw ValidationException::withMessages(['password' => 'Password dosen gagal disinkronkan ke PAS.']);
            }
        }

        $user->update(['password' => $password, 'must_change_password' => false]);
        $request->session()->regenerate();
        $recordAuditLog->handle('profile.password_updated', $user, 'Pengguna memperbarui password sendiri');

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
