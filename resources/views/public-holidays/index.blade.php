@extends('layouts.app')
@section('title', 'Master Tanggal Merah | HRS Kampus')
@section('content')
    <a href="{{ route('leave-requests.index') }}" class="text-sm font-semibold text-brand-700">← Cuti & Persetujuan</a>
    <h1 class="mt-3 text-3xl font-semibold">Master Tanggal Merah</h1>
    <p class="mt-2 text-sm text-slate-500">Tanggal di sini otomatis dikecualikan dari perhitungan durasi cuti.</p>

    <section class="mt-6 rounded-2xl border border-brand-100 bg-brand-50 p-5 shadow-sm">
        <div class="flex flex-col justify-between gap-5 md:flex-row md:items-center">
            <div class="max-w-2xl">
                <div class="flex items-center gap-2"><span class="grid size-9 place-items-center rounded-xl bg-brand-600 font-bold text-white">ID</span><h2 class="font-semibold text-brand-900">Kalender Indonesia Otomatis</h2></div>
                <p class="mt-3 text-sm leading-6 text-brand-800/80">Ambil hari libur nasional dan cuti bersama dari Kalender Indonesia. Sinkronisasi otomatis dijalankan setiap awal bulan, dan dapat dijalankan manual kapan saja.</p>
            </div>
            <form method="POST" action="{{ route('public-holidays.sync.store') }}" class="flex w-full flex-col gap-2 sm:flex-row md:w-auto">
                @csrf
                <label class="sr-only" for="sync-year">Tahun kalender</label>
                <select id="sync-year" name="year" class="rounded-xl border border-brand-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                    @foreach (range(now()->year - 1, now()->year + 3) as $year)
                        <option value="{{ $year }}" @selected(old('year', now()->year) === $year)>{{ $year }}</option>
                    @endforeach
                </select>
                <button type="submit" class="whitespace-nowrap rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-700">Sinkronkan Kalender</button>
            </form>
        </div>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-[22rem_1fr]">
        <form method="POST" action="{{ route('public-holidays.store') }}" class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            <h2 class="font-semibold">Tambah tanggal manual</h2>
            <p class="mt-2 text-xs leading-5 text-slate-400">Gunakan untuk hari libur internal kampus atau koreksi khusus.</p>
            <label class="mt-4 block text-sm font-semibold">Tanggal<input type="date" name="holiday_date" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label>
            <label class="mt-4 block text-sm font-semibold">Nama hari libur<input name="name" required placeholder="Contoh: Libur Dies Natalis" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label>
            <button class="mt-5 w-full rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Simpan Manual</button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[36rem] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Tanggal</th><th class="px-5 py-3">Hari libur</th><th class="px-5 py-3"><span class="sr-only">Aksi</span></th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($holidays as $holiday)
                            <tr><td class="px-5 py-4 font-semibold">{{ $holiday->holiday_date->locale('id')->translatedFormat('d F Y') }}</td><td class="px-5 py-4">{{ $holiday->name }}</td><td class="px-5 py-4 text-right"><form method="POST" action="{{ route('public-holidays.destroy', $holiday) }}" onsubmit="return confirm('Hapus tanggal merah ini?')">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-600 hover:text-red-700">Hapus</button></form></td></tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-12 text-center text-slate-400">Belum ada tanggal merah.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">{{ $holidays->links() }}</div>
        </div>
    </div>
@endsection
