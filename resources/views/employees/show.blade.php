@extends('layouts.app')
@section('title', 'Detail Staff | HRS Kampus')
@section('content')
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
        <div><a href="{{ route('employees.index') }}" class="text-sm font-semibold text-brand-700">← Manajemen User</a><h1 class="mt-3 text-3xl font-semibold">Detail Staff</h1></div>
        <a href="{{ route('employees.edit', $employee) }}" class="rounded-xl bg-brand-600 px-5 py-2.5 text-center text-sm font-semibold text-white">Edit Data</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[20rem_1fr]">
        <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm">
            <span class="mx-auto grid size-20 place-items-center rounded-2xl bg-brand-50 text-2xl font-bold text-brand-700">{{ strtoupper(substr($employee->full_name, 0, 2)) }}</span>
            <h2 class="mt-4 text-xl font-semibold">{{ $employee->display_name }}</h2><p class="mt-1 text-sm text-slate-500">{{ $employee->position?->name }}</p>
            <span class="mt-4 inline-block rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold uppercase text-brand-700">{{ $employee->user?->role ?? 'Belum ada akun' }}</span>
            <div class="mt-6 border-t border-slate-100 pt-5 text-left text-sm"><p class="text-slate-400">Masa kerja</p><p class="mt-1 font-semibold">{{ $employee->length_of_service }}</p><p class="mt-4 text-slate-400">Status</p><p class="mt-1 font-semibold">{{ $employee->status === 'active' ? 'Aktif' : 'Nonaktif' }}</p></div>
        </aside>
        <div class="flex flex-col gap-5">
            @php
                $sections = [
                    'Identitas & Kontak' => [
                        'NIP' => $employee->nip, 'NIK' => $employee->nik, 'Jenis kelamin' => $employee->gender === 'male' ? 'Laki-laki' : 'Perempuan',
                        'Tanggal lahir' => $employee->date_of_birth?->format('d/m/Y'), 'Email aktif' => $employee->email, 'Nomor HP/WA' => $employee->phone,
                        'NPWP' => $employee->npwp ?: '-', 'Nama ibu kandung' => $employee->mother_name,
                    ],
                    'Kepegawaian' => [
                        'Tanggal masuk' => $employee->joined_on->format('d/m/Y'), 'Departemen' => $employee->department->name, 'Jabatan' => $employee->position?->name,
                        'Atasan langsung' => $employee->supervisor?->display_name ?? '-', 'Jenis kepegawaian' => $employee->employment_type,
                        'NIDN' => $employee->nidn ?: '-', 'BPJS Kesehatan' => $employee->bpjs_health_number ?: '-', 'BPJS Ketenagakerjaan' => $employee->bpjs_employment_number ?: '-', 'Jatah cuti' => $employee->annual_leave_days.' hari', 'Cuti terpakai '.now()->year => (int) $employee->approved_leave_days.' hari', 'Sisa cuti '.now()->year => max(0, $employee->annual_leave_days - (int) $employee->approved_leave_days).' hari',
                    ],
                    'Pendidikan & Penghasilan' => [
                        'Pendidikan terakhir' => $employee->last_education, 'Perguruan tinggi' => $employee->university, 'Program studi' => $employee->study_program,
                        'Gaji pokok' => 'Rp '.number_format((float) $employee->base_salary, 0, ',', '.'), 'Tunjangan transport' => 'Rp '.number_format((float) $employee->transport_allowance, 0, ',', '.'),
                    ],
                ];
            @endphp
            @foreach ($sections as $title => $items)
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6"><h2 class="font-semibold">{{ $title }}</h2><dl class="mt-5 grid gap-x-8 gap-y-5 sm:grid-cols-2">@foreach ($items as $name => $content)<div><dt class="text-xs uppercase tracking-wide text-slate-400">{{ $name }}</dt><dd class="mt-1 text-sm font-medium text-slate-700">{{ $content ?: '-' }}</dd></div>@endforeach</dl></section>
            @endforeach
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6"><h2 class="font-semibold">Alamat</h2><div class="mt-5 grid gap-5 md:grid-cols-2"><div><p class="text-xs uppercase text-slate-400">Alamat KTP</p><p class="mt-2 whitespace-pre-line text-sm leading-6">{{ $employee->identity_address }}</p></div><div><p class="text-xs uppercase text-slate-400">Alamat rumah</p><p class="mt-2 whitespace-pre-line text-sm leading-6">{{ $employee->residential_address }}</p></div></div></section>
        </div>
    </div>
@endsection
