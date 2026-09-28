@php
    $input = 'mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
@endphp
<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="grid gap-5 md:grid-cols-2">
        <label class="text-sm font-semibold">Kode departemen <span class="text-red-500">*</span><input name="code" maxlength="20" value="{{ old('code', $department?->code) }}" placeholder="Contoh: BAAK" class="{{ $input }}">@error('code')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
        <label class="text-sm font-semibold">Nama departemen <span class="text-red-500">*</span><input name="name" value="{{ old('name', $department?->name) }}" placeholder="Contoh: Biro Administrasi Akademik" class="{{ $input }}">@error('name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
        <label class="text-sm font-semibold">Unit induk<select name="parent_id" class="{{ $input }}"><option value="">Tidak ada / unit utama</option>@foreach ($parents as $parent)<option value="{{ $parent->id }}" @selected((string) old('parent_id', $department?->parent_id) === (string) $parent->id)>{{ $parent->name }} ({{ $parent->code }})</option>@endforeach</select>@error('parent_id')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
        <label class="text-sm font-semibold">Jenis unit <span class="text-red-500">*</span><select name="type" class="{{ $input }}">@foreach (['leadership' => 'Pimpinan', 'faculty' => 'Fakultas', 'program_study' => 'Program Studi', 'administration' => 'Administrasi', 'support' => 'Pendukung', 'work_unit' => 'Unit Kerja'] as $key => $name)<option value="{{ $key }}" @selected(old('type', $department?->type ?? 'work_unit') === $key)>{{ $name }}</option>@endforeach</select></label>
        <label class="flex items-center gap-3 text-sm font-semibold md:col-span-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $department?->is_active ?? true)) class="size-5 rounded border-slate-300 text-brand-600"> Departemen aktif</label>
    </div>
</div>
<div class="mt-5 flex justify-end gap-3"><a href="{{ route('departments.index') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold">Batal</a><button class="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white">Simpan Departemen</button></div>
