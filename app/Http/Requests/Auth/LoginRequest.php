<?php

namespace App\Http\Requests\Auth;

use App\Actions\AuthenticatePasLecturerAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(AuthenticatePasLecturerAction $authenticatePasLecturer): void
    {
        if (! $authenticatePasLecturer->handle(
            $this->string('email')->trim()->toString(),
            $this->string('password')->toString(),
            $this->boolean('remember'),
        )) {
            throw ValidationException::withMessages([
                'email' => 'Email, NIDN, kode dosen, atau password tidak sesuai. Pastikan data dosen sudah terdaftar di HRS.',
            ]);
        }
    }
}
