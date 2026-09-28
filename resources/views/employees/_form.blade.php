@if ($errors->any())
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">Mohon periksa kembali data yang ditandai.</div>
@endif

@php
    $input = 'mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
    $label = 'text-sm font-semibold text-slate-700';
    $value = fn (string $key, mixed $default = '') => old($key, data_get($employee, $key, $default));
@endphp

<div class="flex flex-col gap-6">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
        <h2 class="text-lg font-semibold">Identitas Pribadi</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <label class="{{ $label }} lg:col-span-2">Nama lengkap <span class="text-red-500">*</span><input name="full_name" value="{{ $value('full_name') }}" class="{{ $input }}">@error('full_name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <label class="{{ $label }}">Jenis kelamin <span class="text-red-500">*</span><select name="gender" class="{{ $input }}"><option value="">Pilih</option><option value="male" @selected($value('gender') === 'male')>Laki-laki</option><option value="female" @selected($value('gender') === 'female')>Perempuan</option></select>@error('gender')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <label class="{{ $label }}">Gelar depan<input name="title_prefix" value="{{ $value('title_prefix') }}" placeholder="Dr., Prof." class="{{ $input }}"></label>
            <label class="{{ $label }}">Gelar belakang<input name="title_suffix" value="{{ $value('title_suffix') }}" placeholder="S.Kom., M.Kom." class="{{ $input }}"></label>
            <label class="{{ $label }}">Tanggal lahir <span class="text-red-500">*</span><input type="date" name="date_of_birth" value="{{ old('date_of_birth', $employee?->date_of_birth?->format('Y-m-d')) }}" class="{{ $input }}">@error('date_of_birth')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <label class="{{ $label }}">NIK <span class="text-red-500">*</span><input name="nik" inputmode="numeric" value="{{ $value('nik') }}" class="{{ $input }}">@error('nik')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <label class="{{ $label }}">NPWP <span class="font-normal text-slate-400">(opsional)</span><input name="npwp" value="{{ $value('npwp') }}" class="{{ $input }}">@error('npwp')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <label class="{{ $label }}">Nama ibu kandung <span class="text-red-500">*</span><input name="mother_name" value="{{ $value('mother_name') }}" class="{{ $input }}">@error('mother_name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
        <h2 class="text-lg font-semibold">Kepegawaian & Akun</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <label class="{{ $label }}">NIP <span class="text-red-500">*</span><input name="nip" value="{{ $value('nip') }}" class="{{ $input }}">@error('nip')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <label class="{{ $label }}">Email aktif / Username <span class="text-red-500">*</span><input type="email" name="email" value="{{ $value('email') }}" class="{{ $input }}">@error('email')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <label class="{{ $label }}">Role <span class="text-red-500">*</span><select name="role" class="{{ $input }}">@foreach (['admin' => 'Administrator', 'hr' => 'SDM/HR', 'dosen' => 'Dosen', 'staff' => 'Staff'] as $key => $text)<option value="{{ $key }}" @selected(old('role', $employee?->user?->role ?? 'staff') === $key)>{{ $text }}</option>@endforeach</select>@error('role')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <label class="{{ $label }}">Tanggal masuk <span class="text-red-500">*</span><input type="date" name="joined_on" value="{{ old('joined_on', $employee?->joined_on?->format('Y-m-d')) }}" class="{{ $input }}"><span class="mt-1 block text-xs font-normal text-slate-400">Masa kerja dihitung otomatis.</span>@error('joined_on')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <label class="{{ $label }}">Departemen <span class="text-red-500">*</span><select name="department_id" class="{{ $input }}"><option value="">Pilih departemen</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected((string) $value('department_id') === (string) $department->id)>{{ $department->name }}</option>@endforeach</select>@error('department_id')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <label class="{{ $label }}">Jabatan <span class="text-red-500">*</span><select name="position_id" class="{{ $input }}"><option value="">Pilih jabatan</option>@foreach ($positions as $position)<option value="{{ $position->id }}" @selected((string) $value('position_id') === (string) $position->id)>{{ $position->name }}</option>@endforeach</select>@error('position_id')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <label class="{{ $label }}">Atasan langsung<select name="supervisor_id" class="{{ $input }}"><option value="">Tidak ada</option>@foreach ($supervisors as $supervisor)<option value="{{ $supervisor->id }}" @selected((string) $value('supervisor_id') === (string) $supervisor->id)>{{ $supervisor->display_name }}</option>@endforeach</select>@error('supervisor_id')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <label class="{{ $label }}">Jenis kepegawaian <select name="employment_type" class="{{ $input }}">@foreach (['permanent' => 'Tetap', 'contract' => 'Kontrak', 'part_time' => 'Paruh waktu'] as $key => $text)<option value="{{ $key }}" @selected($value('employment_type', 'permanent') === $key)>{{ $text }}</option>@endforeach</select></label>
            <label class="{{ $label }}">Status <select name="status" class="{{ $input }}"><option value="active" @selected($value('status', 'active') === 'active')>Aktif</option><option value="inactive" @selected($value('status') === 'inactive')>Nonaktif</option></select></label>
            <label class="{{ $label }}">NIDN <span class="font-normal text-slate-400">(khusus dosen)</span><input name="nidn" value="{{ $value('nidn') }}" class="{{ $input }}">@error('nidn')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <label class="{{ $label }}">BPJS Kesehatan<input name="bpjs_health_number" value="{{ $value('bpjs_health_number') }}" class="{{ $input }}"></label>
            <label class="{{ $label }}">BPJS Ketenagakerjaan<input name="bpjs_employment_number" value="{{ $value('bpjs_employment_number') }}" class="{{ $input }}"></label>
            <label class="{{ $label }}">Jatah cuti (hari) <span class="text-red-500">*</span><input type="number" min="0" name="annual_leave_days" value="{{ $value('annual_leave_days', 12) }}" class="{{ $input }}"></label>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
        <h2 class="text-lg font-semibold">Kontak & Alamat</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <label class="{{ $label }}">Nomor HP / WhatsApp <span class="text-red-500">*</span><input name="phone" value="{{ $value('phone') }}" class="{{ $input }}">@error('phone')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <div></div>
            <label class="{{ $label }}">Alamat KTP <span class="text-red-500">*</span><textarea id="identity-address" name="identity_address" rows="4" class="{{ $input }}">{{ $value('identity_address') }}</textarea>@error('identity_address')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <div><label class="{{ $label }}">Alamat rumah <span class="text-red-500">*</span><textarea id="residential-address" name="residential_address" rows="4" class="{{ $input }}">{{ $value('residential_address') }}</textarea></label><label class="mt-2 flex items-center gap-2 text-sm text-slate-600"><input id="same-address" type="checkbox" name="same_as_identity_address" value="1" class="size-4 rounded border-slate-300 text-brand-600"> Sama dengan alamat KTP</label>@error('residential_address')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
        <h2 class="text-lg font-semibold">Pendidikan & Penghasilan</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <label class="{{ $label }}">Pendidikan terakhir <select name="last_education" class="{{ $input }}">@foreach (['SMA/SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'] as $education)<option value="{{ $education }}" @selected($value('last_education') === $education)>{{ $education }}</option>@endforeach</select></label>
            <label class="{{ $label }}">Perguruan tinggi <span class="text-red-500">*</span><input name="university" value="{{ $value('university') }}" class="{{ $input }}"></label>
            <label class="{{ $label }}">Program studi <span class="text-red-500">*</span><input name="study_program" value="{{ $value('study_program') }}" class="{{ $input }}"></label>
            <label class="{{ $label }}">Gaji pokok <span class="text-red-500">*</span><input type="number" min="0" name="base_salary" value="{{ $value('base_salary', 0) }}" class="{{ $input }}"></label>
            <label class="{{ $label }}">Tunjangan transport <span class="text-red-500">*</span><input type="number" min="0" name="transport_allowance" value="{{ $value('transport_allowance', 0) }}" class="{{ $input }}"></label>
        </div>
    </section>

    <div class="flex justify-end gap-3"><a href="{{ route('employees.index') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700">Batal</a><button class="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white">Simpan Data</button></div>
</div>

@push('scripts')
<script>
    const checkbox = document.querySelector('#same-address');
    const identityAddress = document.querySelector('#identity-address');
    const residentialAddress = document.querySelector('#residential-address');
    checkbox?.addEventListener('change', () => {
        residentialAddress.disabled = checkbox.checked;
        if (checkbox.checked) residentialAddress.value = identityAddress.value;
    });
    identityAddress?.addEventListener('input', () => {
        if (checkbox.checked) residentialAddress.value = identityAddress.value;
    });
</script>
@endpush
