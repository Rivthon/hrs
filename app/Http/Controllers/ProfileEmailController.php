<?php

namespace App\Http\Controllers;

use App\Actions\RecordAuditLogAction;
use App\Http\Requests\UpdateProfileEmailRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProfileEmailController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateProfileEmailRequest $request, RecordAuditLogAction $recordAuditLog): RedirectResponse
    {
        $user = $request->user();
        $employee = $user->employee;
        $email = $request->validated('email');

        if ($user->role === 'dosen') {
            try {
                $pasLecturer = DB::connection('pas')->table('dosen')->where('dosen_id', $employee->pas_dosen_id);
                if (! $pasLecturer->exists()) {
                    throw ValidationException::withMessages(['email' => 'Data dosen tidak ditemukan di PAS.']);
                }
                $pasLecturer->update(['email' => $email]);
            } catch (ValidationException $exception) {
                throw $exception;
            } catch (Throwable $exception) {
                report($exception);
                throw ValidationException::withMessages(['email' => 'Email dosen gagal disinkronkan ke PAS.']);
            }
        }

        $oldEmail = $user->email;
        DB::transaction(function () use ($user, $employee, $email): void {
            $user->update(['email' => $email]);
            $employee->update(['email' => $email]);
        });
        $recordAuditLog->handle('profile.email_updated', $user, 'Pengguna memperbarui email sendiri', ['email' => $oldEmail], ['email' => $email]);

        return back()->with('success', 'Email berhasil diperbarui.');
    }
}
