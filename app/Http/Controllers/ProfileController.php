<?php

namespace App\Http\Controllers;

use App\Actions\RecordAuditLogAction;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $employee = $request->user()->employee()->with(['department', 'position'])->firstOrFail();

        return view('profile.edit', compact('employee'));
    }

    public function update(UpdateProfileRequest $request, RecordAuditLogAction $recordAuditLog): RedirectResponse
    {
        $employee = $request->user()->employee;
        $oldValues = $employee->only(array_keys($request->validated()));

        if ($request->user()->role === 'dosen') {
            try {
                $pasLecturer = DB::connection('pas')->table('dosen')->where('dosen_id', $employee->pas_dosen_id);
                if (! $pasLecturer->exists()) {
                    throw ValidationException::withMessages(['full_name' => 'Data dosen tidak ditemukan di PAS.']);
                }
                $pasLecturer->update(['nama' => $request->validated('full_name')]);
            } catch (ValidationException $exception) {
                throw $exception;
            } catch (Throwable $exception) {
                report($exception);
                throw ValidationException::withMessages(['full_name' => 'Nama dosen gagal disinkronkan ke PAS.']);
            }
        }

        $employee->update($request->validated());
        $request->user()->update(['name' => $employee->full_name]);
        $recordAuditLog->handle('profile.updated', $employee, 'Pengguna memperbarui biodata sendiri', $oldValues, $employee->only(array_keys($oldValues)));

        return back()->with('success', 'Biodata berhasil diperbarui.');
    }
}
