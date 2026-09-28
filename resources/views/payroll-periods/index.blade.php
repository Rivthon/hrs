@extends('layouts.app')
@section('title', 'Payroll | HRS Kampus')
@section('content')
    <div>
        <p class="text-sm font-semibold text-brand-600">Keuangan SDM</p>
        <h1 class="mt-1 text-3xl font-semibold">Payroll</h1>
        <p class="mt-2 text-sm text-slate-500">Buat periode bulanan dan kelola komponen penghasilan serta potongan karyawan.</p>
    </div>
    <div class="mt-6 grid gap-6 lg:grid-cols-[22rem_1fr]">
        <form method="POST" action="{{ route('payroll-periods.store') }}" class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            <h2 class="font-semibold">Buat periode payroll</h2>
            <label class="mt-4 block text-sm font-semibold">Bulan payroll<input type="month" name="period" required value="{{ old('period', now()->format('Y-m')) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label>
            <p class="mt-3 text-xs leading-5 text-slate-400">Gaji pokok dan tunjangan transport seluruh pegawai aktif akan dimuat otomatis.</p>
            <button class="mt-5 w-full rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white">Buat & Proses</button>
        </form>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[48rem] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Periode</th><th class="px-5 py-3">Pegawai</th><th class="px-5 py-3">Total gaji bersih</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"><span class="sr-only">Aksi</span></th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($periods as $period)
                            <tr>
                                <td class="px-5 py-4 font-semibold">{{ $period->period_date->locale('id')->translatedFormat('F Y') }}</td>
                                <td class="px-5 py-4">{{ $period->payrolls_count }}</td>
                                <td class="px-5 py-4 font-semibold">Rp {{ number_format((float) ($period->payrolls_sum_net_salary ?? 0), 0, ',', '.') }}</td>
                                <td class="px-5 py-4"><span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold uppercase text-amber-700">{{ $period->status }}</span></td>
                                <td class="px-5 py-4"><div class="flex items-center justify-end gap-3"><a href="{{ route('payroll-periods.show', $period) }}" class="font-semibold text-brand-700">Kelola</a><form method="POST" action="{{ route('payroll-periods.destroy', $period) }}" onsubmit="return confirm('Hapus periode payroll {{ $period->period_date->format('m/Y') }} beserta seluruh rinciannya?')">@csrf @method('DELETE')<button type="submit" class="font-semibold text-red-600 hover:text-red-700">Hapus</button></form></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-12 text-center text-slate-400">Belum ada periode payroll.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">{{ $periods->links() }}</div>
        </div>
    </div>
@endsection
