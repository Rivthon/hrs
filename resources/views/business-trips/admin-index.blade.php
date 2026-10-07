@extends('layouts.app')
@section('title', 'Penugasan Perjalanan Dinas | HRS Kampus')
@section('content')
    <div><p class="text-sm font-semibold text-brand-600">Administrasi SDM</p><h1 class="mt-1 text-3xl font-semibold">Penugasan Perjalanan Dinas</h1><p class="mt-2 text-sm text-slate-500">Buat penugasan, pilih pegawai, dan pantau penerimaan serta laporan hasil perjalanan.</p></div>

    <section class="mt-7 grid gap-6 xl:grid-cols-[1fr_1.4fr]">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Buat Penugasan Baru</h2>
            <form method="POST" action="{{ route('business-trips.store') }}" class="mt-5 flex flex-col gap-4">@csrf
                <label class="text-sm font-semibold text-slate-600">Ditugaskan kepada<select name="employee_id" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm"><option value="">Pilih pegawai</option>@foreach ($employees as $employee)<option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->display_name }} · {{ $employee->department->name }}</option>@endforeach</select></label>
                <label class="text-sm font-semibold text-slate-600">Nama kegiatan<input name="title" value="{{ old('title') }}" required maxlength="255" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm" placeholder="Contoh: Rapat koordinasi wilayah"></label>
                <label class="text-sm font-semibold text-slate-600">Tujuan / lokasi<input name="destination" value="{{ old('destination') }}" required maxlength="255" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm" placeholder="Kota atau instansi tujuan"></label>
                <label class="text-sm font-semibold text-slate-600">Maksud perjalanan<textarea name="purpose" rows="3" required maxlength="5000" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">{{ old('purpose') }}</textarea></label>
                <div class="grid gap-4 sm:grid-cols-2"><label class="text-sm font-semibold text-slate-600">Tanggal berangkat<input type="date" name="start_date" value="{{ old('start_date') }}" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm"></label><label class="text-sm font-semibold text-slate-600">Tanggal kembali<input type="date" name="end_date" value="{{ old('end_date') }}" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm"></label></div>
                <div class="grid gap-4 sm:grid-cols-2"><label class="text-sm font-semibold text-slate-600">Transportasi<input name="transportation" value="{{ old('transportation') }}" maxlength="255" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm" placeholder="Pesawat, kereta, kendaraan dinas"></label><label class="text-sm font-semibold text-slate-600">Uang perjalanan (Rp)<input type="number" name="allowance" value="{{ old('allowance', 0) }}" min="0" step="1000" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm"></label></div>
                <label class="text-sm font-semibold text-slate-600">Catatan penugasan<textarea name="assignment_notes" rows="2" maxlength="5000" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">{{ old('assignment_notes') }}</textarea></label>
                <button class="rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white">Kirim Penugasan</button>
            </form>
        </article>

        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5"><h2 class="text-lg font-semibold">Daftar Penugasan</h2><p class="mt-1 text-sm text-slate-500">Status penerimaan dan laporan pegawai</p></div>
            <div class="divide-y divide-slate-100">
                @forelse ($businessTrips as $businessTrip)
                    <a href="{{ route('business-trips.show', $businessTrip) }}" class="block px-6 py-4 hover:bg-slate-50"><div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start"><div><p class="font-semibold">{{ $businessTrip->title }}</p><p class="mt-1 text-sm text-slate-600">{{ $businessTrip->employee->display_name }} · {{ $businessTrip->destination }}</p><p class="mt-1 text-xs text-slate-400">{{ $businessTrip->start_date->format('d/m/Y') }}–{{ $businessTrip->end_date->format('d/m/Y') }} · {{ $businessTrip->employee->department->name }}</p></div><span @class(['w-fit rounded-full px-3 py-1 text-xs font-semibold', 'bg-amber-50 text-amber-700' => $businessTrip->status === 'assigned', 'bg-sky-50 text-sky-700' => $businessTrip->status === 'accepted', 'bg-red-50 text-red-700' => $businessTrip->status === 'rejected', 'bg-brand-50 text-brand-700' => $businessTrip->status === 'reported'])>{{ $businessTrip->statusLabel() }}</span></div></a>
                @empty
                    <p class="px-6 py-12 text-center text-sm text-slate-400">Belum ada penugasan perjalanan dinas.</p>
                @endforelse
            </div>
            <div class="border-t border-slate-100 px-6 py-4">{{ $businessTrips->links() }}</div>
        </article>
    </section>
@endsection
