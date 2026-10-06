<?php

namespace App\Actions;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class AuthenticatePasLecturerAction
{
    public function handle(string $identifier, string $password, bool $remember = false): bool
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $localUser = User::where('email', $identifier)->first();

            if ($localUser && $localUser->role !== 'dosen') {
                return Auth::attempt(['email' => $identifier, 'password' => $password], $remember);
            }
        }

        try {
            $pasLecturer = DB::connection('pas')
                ->table('dosen')
                ->where(function ($query) use ($identifier): void {
                    $query->where('email', $identifier)
                        ->orWhere('nidn', $identifier)
                        ->orWhere('kd_dosen', $identifier);
                })
                ->first();
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }

        if (! $pasLecturer || blank($pasLecturer->password) || ! Hash::check($password, $pasLecturer->password)) {
            return false;
        }

        $employee = Employee::query()
            ->where(function ($query) use ($pasLecturer): void {
                $query->where('pas_dosen_id', $pasLecturer->dosen_id)
                    ->when(filled($pasLecturer->nidn), fn ($query) => $query->orWhere('nidn', $pasLecturer->nidn))
                    ->orWhere('email', $pasLecturer->email);
            })
            ->with('user')
            ->first();

        if (! $employee) {
            return false;
        }

        $user = $employee->user;

        if (! $user) {
            return false;
        }

        $user->update([
            'name' => $pasLecturer->nama,
            'email' => $pasLecturer->email,
            'password' => $password,
            'role' => 'dosen',
            'must_change_password' => false,
        ]);
        $employee->update([
            'pas_dosen_id' => $pasLecturer->dosen_id,
            'pas_kode_dosen' => $pasLecturer->kd_dosen,
            'full_name' => $pasLecturer->nama,
            'email' => $pasLecturer->email,
            'nidn' => $pasLecturer->nidn ?: $employee->nidn,
        ]);

        Auth::login($user->refresh(), $remember);

        return true;
    }
}
