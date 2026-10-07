@extends('layouts.app')
@section('title', 'Profil Saya | HRS Kampus')
@section('content')
    @php
        $input = 'mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
        $label = 'block text-sm font-semibold text-slate-700';
    @endphp
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end"><div><p class="text-sm font-semibold text-brand-600">Akun pribadi</p><h1 class="mt-1 text-3xl font-semibold">Profil Saya</h1><p class="mt-2 text-sm text-slate-500">Kelola biodata, email login, dan keamanan akun Anda.</p></div><div class="rounded-xl bg-slate-100 px-4 py-3 text-sm"><span class="text-slate-500">NIP/NIDN:</span> <span class="font-semibold">{{ $employee->nip }}{{ $employee->nidn ? ' / '.$employee->nidn : '' }}</span></div></div>

    <div class="mt-7 grid gap-6 xl:grid-cols-[1.5fr_1fr]">
        <form method="POST" action="{{ route('profile.update') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
            @csrf @method('PUT')
            <div><h2 class="text-lg font-semibold">Biodata Pribadi</h2><p class="mt-1 text-sm text-slate-500">Departemen, jabatan, NIP/NIDN, gaji, dan jatah cuti hanya dapat diubah oleh SDM.</p></div>
            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <label class="{{ $label }} md:col-span-2">Nama lengkap<input name="full_name" value="{{ old('full_name', $employee->full_name) }}" required class="{{ $input }}">@error('full_name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
                <label class="{{ $label }}">Gelar depan<input name="title_prefix" value="{{ old('title_prefix', $employee->title_prefix) }}" class="{{ $input }}"></label>
                <label class="{{ $label }}">Gelar belakang<input name="title_suffix" value="{{ old('title_suffix', $employee->title_suffix) }}" class="{{ $input }}"></label>
                <label class="{{ $label }}">Jenis kelamin<select name="gender" class="{{ $input }}"><option value="male" @selected(old('gender', $employee->gender) === 'male')>Laki-laki</option><option value="female" @selected(old('gender', $employee->gender) === 'female')>Perempuan</option></select></label>
                <label class="{{ $label }}">Tanggal lahir<input type="date" name="date_of_birth" value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}" required class="{{ $input }}"></label>
                <label class="{{ $label }}">Nomor HP/WhatsApp<input name="phone" value="{{ old('phone', $employee->phone) }}" required class="{{ $input }}"></label>
                <label class="{{ $label }}">Nama ibu kandung<input name="mother_name" value="{{ old('mother_name', $employee->mother_name) }}" required class="{{ $input }}"></label>
                <label class="{{ $label }}">Pendidikan terakhir<select name="last_education" class="{{ $input }}">@foreach (['SMA/SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'] as $education)<option value="{{ $education }}" @selected(old('last_education', $employee->last_education) === $education)>{{ $education }}</option>@endforeach</select></label>
                <label class="{{ $label }}">Perguruan tinggi<input name="university" value="{{ old('university', $employee->university) }}" required class="{{ $input }}"></label>
                <label class="{{ $label }} md:col-span-2">Program studi<input name="study_program" value="{{ old('study_program', $employee->study_program) }}" required class="{{ $input }}"></label>
                <label class="{{ $label }}">Alamat KTP<textarea name="identity_address" rows="4" required class="{{ $input }}">{{ old('identity_address', $employee->identity_address) }}</textarea></label>
                <label class="{{ $label }}">Alamat rumah<textarea name="residential_address" rows="4" required class="{{ $input }}">{{ old('residential_address', $employee->residential_address) }}</textarea></label>
            </div>
            <div class="mt-6 flex justify-end"><button class="rounded-xl bg-brand-600 px-6 py-3 text-sm font-semibold text-white">Simpan Biodata</button></div>
        </form>

        <div class="flex flex-col gap-6">
            <form method="POST" action="{{ route('profile.email.update') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
                @csrf @method('PUT')
                <h2 class="text-lg font-semibold">Email Login</h2><p class="mt-1 text-sm text-slate-500">Untuk dosen, email juga akan diperbarui di PAS.</p>
                <div class="mt-5 flex flex-col gap-4"><label class="{{ $label }}">Email aktif<input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required class="{{ $input }}">@error('email')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label><label class="{{ $label }}">Password saat ini<input type="password" name="current_password" required autocomplete="current-password" class="{{ $input }}">@error('current_password')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label><button class="rounded-xl border border-brand-600 px-5 py-3 text-sm font-semibold text-brand-700">Perbarui Email</button></div>
            </form>
            <form method="POST" action="{{ route('profile.password.update') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
                @csrf @method('PUT')
                <h2 class="text-lg font-semibold">Ganti Password</h2><p class="mt-1 text-sm text-slate-500">Minimal 8 karakter berisi huruf dan angka. Password dosen juga disinkronkan ke PAS.</p>
                <div class="mt-5 flex flex-col gap-4"><label class="{{ $label }}">Password saat ini<input type="password" name="current_password" required autocomplete="current-password" class="{{ $input }}"></label><label class="{{ $label }}">Password baru<input type="password" name="password" required autocomplete="new-password" class="{{ $input }}">@error('password')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label><label class="{{ $label }}">Konfirmasi password baru<input type="password" name="password_confirmation" required autocomplete="new-password" class="{{ $input }}"></label><button class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Ganti Password</button></div>
            </form>
        </div>
    </div>
@endsection
