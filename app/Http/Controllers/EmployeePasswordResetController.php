<?php

namespace App\Http\Controllers;

use App\Actions\RecordAuditLogAction;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class EmployeePasswordResetController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Employee $employee, RecordAuditLogAction $recordAuditLog): RedirectResponse
    {
        $employee->loadMissing('user');
        abort_if($employee->user === null, 422, 'Pegawai belum memiliki akun.');
        abort_if($employee->user->role === 'dosen', 403, 'Password dosen dikelola oleh PAS.');

        $temporaryPassword = Str::password(16);
        $employee->user->update([
            'password' => $temporaryPassword,
            'must_change_password' => false,
        ]);
        $recordAuditLog->handle('password.reset', $employee->user, 'Mereset password '.$employee->full_name);

        return back()
            ->with('success', 'Password berhasil direset.')
            ->with('temporary_password', $temporaryPassword);
    }
}
