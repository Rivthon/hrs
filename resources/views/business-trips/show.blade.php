@extends('layouts.app')
@section('title', 'Detail Perjalanan Dinas | HRS Kampus')
@section('content')
    <a href="{{ route('business-trips.index') }}" class="text-sm font-semibold text-brand-700">← Kembali ke Perjalanan Dinas</a>
    <div class="mt-4 flex flex-col justify-between gap-4 md:flex-row md:items-start"><div><p class="text-sm font-semibold text-brand-600">Surat tugas perjalanan dinas</p><h1 class="mt-1 text-3xl font-semibold">{{ $businessTrip->title }}</h1><p class="mt-2 text-sm text-slate-500">{{ $businessTrip->employee->display_name }} · {{ $businessTrip->employee->department->name }}</p></div><span class="w-fit rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">{{ $businessTrip->statusLabel() }}</span></div>
    <section class="mt-7 grid gap-6 lg:grid-cols-2">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="font-semibold">Detail Penugasan</h2><dl class="mt-5 grid gap-5 sm:grid-cols-2"><div><dt class="text-xs uppercase text-slate-400">Tujuan</dt><dd class="mt-1 font-medium">{{ $businessTrip->destination }}</dd></div><div><dt class="text-xs uppercase text-slate-400">Tanggal</dt><dd class="mt-1 font-medium">{{ $businessTrip->start_date->format('d/m/Y') }}–{{ $businessTrip->end_date->format('d/m/Y') }}</dd></div><div><dt class="text-xs uppercase text-slate-400">Transportasi</dt><dd class="mt-1 font-medium">{{ $businessTrip->transportation ?: '-' }}</dd></div><div><dt class="text-xs uppercase text-slate-400">Uang perjalanan</dt><dd class="mt-1 font-medium">Rp {{ number_format((float) $businessTrip->allowance, 0, ',', '.') }}</dd></div><div class="sm:col-span-2"><dt class="text-xs uppercase text-slate-400">Maksud perjalanan</dt><dd class="mt-1 text-sm leading-6 text-slate-600">{{ $businessTrip->purpose }}</dd></div>@if ($businessTrip->assignment_notes)<div class="sm:col-span-2"><dt class="text-xs uppercase text-slate-400">Catatan penugasan</dt><dd class="mt-1 text-sm leading-6 text-slate-600">{{ $businessTrip->assignment_notes }}</dd></div>@endif</dl></article>
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold">Penerimaan & Laporan</h2>
            <dl class="mt-5 grid gap-5">
                <div><dt class="text-xs uppercase text-slate-400">Respons pegawai</dt><dd class="mt-1 text-sm font-medium">{{ $businessTrip->responded_at ? $businessTrip->statusLabel().' pada '.$businessTrip->responded_at->format('d/m/Y H:i') : 'Belum memberikan respons' }}</dd></div>
                @if ($businessTrip->rejection_reason)
                    <div><dt class="text-xs uppercase text-slate-400">Alasan penolakan</dt><dd class="mt-1 text-sm leading-6 text-red-600">{{ $businessTrip->rejection_reason }}</dd></div>
                @endif
                @if ($businessTrip->reported_at)
                    <div><dt class="text-xs uppercase text-slate-400">Ringkasan kegiatan</dt><dd class="mt-1 text-sm leading-6 text-slate-600">{{ $businessTrip->report_summary }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-400">Hasil perjalanan</dt><dd class="mt-1 text-sm leading-6 text-slate-600">{{ $businessTrip->report_result }}</dd></div>
                    @if ($businessTrip->report_notes)
                        <div><dt class="text-xs uppercase text-slate-400">Catatan laporan</dt><dd class="mt-1 text-sm leading-6 text-slate-600">{{ $businessTrip->report_notes }}</dd></div>
                    @endif
                    @if ($businessTrip->report_image_path)
                        <div><dt class="text-xs uppercase text-slate-400">Foto dokumentasi</dt><dd class="mt-2"><a href="{{ asset('storage/'.$businessTrip->report_image_path) }}" target="_blank"><img src="{{ asset('storage/'.$businessTrip->report_image_path) }}" alt="Dokumentasi {{ $businessTrip->title }}" class="max-h-80 w-full rounded-xl border border-slate-200 object-cover"></a></dd></div>
                    @endif
                    <div><dt class="text-xs uppercase text-slate-400">Dilaporkan</dt><dd class="mt-1 text-sm font-medium">{{ $businessTrip->reported_at->format('d/m/Y H:i') }}</dd></div>
                @else
                    <p class="rounded-xl bg-slate-50 px-4 py-5 text-sm text-slate-400">Laporan perjalanan belum tersedia.</p>
                @endif
            </dl>
        </article>
    </section>
@endsection
