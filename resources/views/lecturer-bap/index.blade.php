@extends('layouts.app')
@section('title', 'BAP Saya | HRS Kampus')
@section('content')
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
        <div>
            <p class="text-sm font-semibold text-brand-700">Terhubung langsung dengan PAS</p>
            <h1 class="mt-1 text-3xl font-semibold">BAP Saya</h1>
            <p class="mt-2 text-sm text-slate-500">{{ $pasLecturer->nama }} · {{ $pasLecturer->nidn ?: $pasLecturer->kd_dosen }}</p>
        </div>
        <form method="GET" class="flex items-end gap-2">
            <label class="text-sm font-semibold text-slate-600">Tahun akademik
                <select name="ta_id" class="mt-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5" onchange="this.form.submit()">
                    @foreach ($academicYears as $academicYear)
                        <option value="{{ $academicYear->ta_id }}" @selected($selectedAcademicYearId === (int) $academicYear->ta_id)>{{ $academicYear->nama }} · {{ $academicYear->semester }}</option>
                    @endforeach
                </select>
            </label>
        </form>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Total Pertemuan</p><p class="mt-2 text-3xl font-semibold">{{ $meetings->count() }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Teori</p><p class="mt-2 text-3xl font-semibold">{{ $meetings->where('jenis', 'Teori')->count() }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Praktik</p><p class="mt-2 text-3xl font-semibold">{{ $meetings->where('jenis', 'Praktik')->count() }}</p></div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[55rem] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Tanggal</th><th class="px-5 py-3">Jenis</th><th class="px-5 py-3">Mata Kuliah</th><th class="px-5 py-3">Materi</th><th class="px-5 py-3">Waktu</th><th class="px-5 py-3">Metode</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($meetings as $meeting)
                        <tr><td class="px-5 py-4 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($meeting->tanggal_pertemuan)->locale('id')->translatedFormat('d M Y') }}</td><td class="px-5 py-4"><span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700">{{ $meeting->jenis }}</span></td><td class="px-5 py-4 font-semibold">{{ $meeting->mata_kuliah }}</td><td class="px-5 py-4"><p>{{ $meeting->topik ?: '-' }}</p><p class="mt-1 text-xs text-slate-400">{{ $meeting->sub_topik }}</p></td><td class="px-5 py-4 whitespace-nowrap">{{ substr($meeting->jam_mulai, 0, 5) }}–{{ substr($meeting->jam_selesai, 0, 5) }}</td><td class="px-5 py-4 uppercase">{{ $meeting->metode_pbm }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400">Belum ada BAP pada tahun akademik ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
