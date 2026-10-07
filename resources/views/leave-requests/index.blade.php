@extends('layouts.app')
@section('title', 'Cuti & Izin | HRS Kampus')
@section('content')
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
        <div><p class="text-sm font-semibold text-brand-600">Layanan pegawai</p><h1 class="mt-1 text-3xl font-semibold tracking-tight">Cuti & Persetujuan</h1><p class="mt-2 text-sm text-slate-500">Cuti tahunan, sakit, melahirkan, izin khusus, setengah hari, dan izin per jam.</p></div>
        <div class="flex gap-2">@can('manage-users')<a href="{{ route('public-holidays.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-semibold">Master Tanggal Merah</a>@endcan<a href="{{ route('leave-requests.create') }}" class="rounded-xl bg-brand-600 px-5 py-2.5 text-center text-sm font-semibold text-white">+ Ajukan Cuti</a></div>
    </div>

    <section class="mt-7 grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Jatah Cuti {{ now()->year }}</p><p class="mt-2 text-3xl font-semibold">{{ $employee->annual_leave_days }} <span class="text-base font-normal text-slate-400">hari</span></p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Sudah Disetujui SDM</p><p class="mt-2 text-3xl font-semibold">{{ (int) $employee->approved_leave_days }} <span class="text-base font-normal text-slate-400">hari</span></p></div>
        <div class="rounded-2xl border border-brand-200 bg-brand-50 p-5 shadow-sm"><p class="text-sm text-brand-700">Sisa Cuti</p><p class="mt-2 text-3xl font-semibold text-brand-700">{{ max(0, $employee->annual_leave_days - (int) $employee->approved_leave_days) }} <span class="text-base font-normal">hari</span></p></div>
    </section>

    @if ($supervisorRequests->isNotEmpty() || $hrRequests->isNotEmpty())
        <section class="mt-7 grid gap-5 xl:grid-cols-2">
            @if ($supervisorRequests->isNotEmpty())
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5"><div class="flex items-center justify-between"><h2 class="font-semibold text-amber-900">Menunggu persetujuan Anda</h2><span class="rounded-full bg-amber-200 px-2.5 py-1 text-xs font-bold text-amber-900">{{ $supervisorRequests->count() }}</span></div><div class="mt-4 flex flex-col gap-3">@foreach ($supervisorRequests as $request)<a href="{{ route('leave-requests.show', $request) }}" class="rounded-xl bg-white p-4 shadow-sm"><p class="font-semibold">{{ $request->employee->display_name }}</p><p class="mt-1 text-xs font-semibold text-amber-700">{{ $request->leave_type->label() }}</p><p class="mt-1 text-sm text-slate-500">{{ $request->start_date->format('d/m/Y') }}–{{ $request->end_date->format('d/m/Y') }} · {{ $request->durationLabel() }}</p></a>@endforeach</div></div>
            @endif
            @if ($hrRequests->isNotEmpty())
                <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5"><div class="flex items-center justify-between"><h2 class="font-semibold text-sky-900">Menunggu persetujuan SDM</h2><span class="rounded-full bg-sky-200 px-2.5 py-1 text-xs font-bold text-sky-900">{{ $hrRequests->count() }}</span></div><div class="mt-4 flex flex-col gap-3">@foreach ($hrRequests as $request)<a href="{{ route('leave-requests.show', $request) }}" class="rounded-xl bg-white p-4 shadow-sm"><p class="font-semibold">{{ $request->employee->display_name }}</p><p class="mt-1 text-xs font-semibold text-sky-700">{{ $request->leave_type->label() }}</p><p class="mt-1 text-sm text-slate-500">Disetujui {{ $request->supervisorApprover?->display_name }} · {{ $request->durationLabel() }}</p></a>@endforeach</div></div>
            @endif
        </section>
    @endif

    <section class="mt-7 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-5"><h2 class="font-semibold">Riwayat pengajuan saya</h2></div>
        <div class="overflow-x-auto"><table class="w-full min-w-[55rem] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Jenis</th><th class="px-5 py-3">Tanggal</th><th class="px-5 py-3">Durasi</th><th class="px-5 py-3">Pengganti</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"></th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse ($myRequests as $request)<tr><td class="px-5 py-4 font-medium">{{ $request->leave_type->label() }}</td><td class="px-5 py-4">{{ $request->start_date->format('d/m/Y') }}–{{ $request->end_date->format('d/m/Y') }}</td><td class="px-5 py-4">{{ $request->durationLabel() }}</td><td class="px-5 py-4">{{ $request->replacement->display_name }}</td><td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold">{{ $request->status->label() }}</span></td><td class="px-5 py-4 text-right"><a href="{{ route('leave-requests.show', $request) }}" class="font-semibold text-brand-700">Detail</a></td></tr>@empty<tr><td colspan="6" class="px-5 py-12 text-center text-slate-400">Belum ada pengajuan cuti atau izin.</td></tr>@endforelse
        </tbody></table></div><div class="border-t border-slate-100 px-5 py-4">{{ $myRequests->links() }}</div>
    </section>
@endsection
