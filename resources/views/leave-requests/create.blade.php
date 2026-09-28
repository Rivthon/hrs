@extends('layouts.app')
@section('title', 'Ajukan Cuti | HRS Kampus')
@section('content')
    <a href="{{ route('leave-requests.index') }}" class="text-sm font-semibold text-brand-700">← Kembali</a><h1 class="mt-3 text-3xl font-semibold">Ajukan Cuti</h1><p class="mt-2 text-sm text-slate-500">Pengajuan akan diteruskan kepada atasan langsung lalu SDM.</p>
    @if ($employee->supervisor === null)<div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">Atasan langsung belum ditentukan. Hubungi SDM sebelum mengajukan cuti.</div>@endif
    <form method="POST" action="{{ route('leave-requests.store') }}" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">@csrf
        @if ($errors->any())<div class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
        <div class="grid gap-5 md:grid-cols-2">
            <label class="text-sm font-semibold">Tanggal mulai <input type="date" name="start_date" value="{{ old('start_date') }}" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"></label>
            <label class="text-sm font-semibold">Tanggal selesai <input type="date" name="end_date" value="{{ old('end_date') }}" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"><span class="mt-1 block text-xs font-normal text-slate-400">Maksimal 3 hari kerja.</span></label>
            <label class="text-sm font-semibold md:col-span-2">Pegawai pengganti <select name="replacement_employee_id" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"><option value="">Pilih pegawai pengganti</option>@foreach ($replacements as $replacement)<option value="{{ $replacement->id }}" @selected((string) old('replacement_employee_id') === (string) $replacement->id)>{{ $replacement->display_name }} — {{ $replacement->department->name }}</option>@endforeach</select></label>
            <label class="text-sm font-semibold md:col-span-2">Alasan cuti <textarea name="reason" rows="5" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">{{ old('reason') }}</textarea></label>
        </div>
        <div class="mt-6 flex justify-end gap-3"><a href="{{ route('leave-requests.index') }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold">Batal</a><button @disabled($employee->supervisor === null) class="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50">Kirim Pengajuan</button></div>
    </form>
@endsection
