@extends('layouts.app')

@section('title', (auth()->user()->role === 'dosen' ? 'Dashboard Dosen' : 'Dashboard Tendik').' | HRS Kampus')

@section('content')
    @include('dashboard._leave-popup', ['employeesOnLeaveToday' => $employeesOnLeaveToday])

    <section class="flex flex-col justify-between gap-5 md:flex-row md:items-end">
        <div>
            <p class="text-sm font-semibold text-brand-600">{{ auth()->user()->role === 'dosen' ? 'Dashboard Dosen' : 'Dashboard Tendik' }}</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight">Selamat datang, {{ $employee->display_name }}</h1>
            <p class="mt-2 text-sm text-slate-500">Kelola pekerjaan, laporan harian, dan kebutuhan cuti Anda dalam satu halaman.</p>
        </div>
        <nav class="flex flex-wrap gap-2 text-xs font-semibold">
            <a href="#profil" class="rounded-full bg-slate-100 px-3 py-2 text-slate-600">Profil</a>
            <a href="#todo" class="rounded-full bg-slate-100 px-3 py-2 text-slate-600">Todo</a>
            <a href="#laporan" class="rounded-full bg-slate-100 px-3 py-2 text-slate-600">Laporan</a>
            <a href="#cuti" class="rounded-full bg-slate-100 px-3 py-2 text-slate-600">Cuti</a>
            @if (auth()->user()->role === 'dosen')<a href="#bap" class="rounded-full bg-brand-50 px-3 py-2 text-brand-700">BAP PAS</a>@endif
        </nav>
    </section>

    @if ($pendingBusinessTrips->isNotEmpty())
        <section class="mt-7 rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"><div><p class="text-sm font-semibold text-amber-900">Anda memiliki {{ $pendingBusinessTrips->count() }} penugasan perjalanan dinas baru</p><p class="mt-1 text-xs text-amber-700">Silakan periksa rincian tugas dan berikan konfirmasi penerimaan.</p></div><a href="{{ route('business-trips.index') }}" class="w-fit rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white">Lihat Penugasan</a></div>
            <div class="mt-4 grid gap-3 md:grid-cols-3">@foreach ($pendingBusinessTrips as $businessTrip)<div class="rounded-xl bg-white p-4"><p class="text-sm font-semibold">{{ $businessTrip->title }}</p><p class="mt-1 text-xs text-slate-500">{{ $businessTrip->destination }} · {{ $businessTrip->start_date->format('d/m/Y') }}</p></div>@endforeach</div>
        </section>
    @endif

    @if ($replacementLeaveAssignments->isNotEmpty())
        <section class="mt-7 rounded-2xl border border-sky-200 bg-sky-50 p-5 shadow-sm">
            <div><p class="text-sm font-semibold text-sky-900">Notifikasi Pengganti Cuti</p><p class="mt-1 text-xs text-sky-700">Anda ditunjuk sebagai pegawai pengganti pada pengajuan berikut.</p></div>
            <div class="mt-4 flex flex-col gap-3">
                @foreach ($replacementLeaveAssignments as $leaveAssignment)
                    <div class="rounded-xl border border-sky-100 bg-white p-4"><p class="text-sm font-semibold text-slate-800">{{ $leaveAssignment->employee->display_name }} mengajukan cuti dan Anda menjadi penggantinya.</p><p class="mt-1 text-xs text-slate-500">{{ $leaveAssignment->leave_type->label() }} · {{ $leaveAssignment->start_date->format('d/m/Y') }}–{{ $leaveAssignment->end_date->format('d/m/Y') }} · {{ $leaveAssignment->status->label() }}</p></div>
                @endforeach
            </div>
        </section>
    @endif

    <section id="profil" class="mt-7 grid gap-5 xl:grid-cols-[1.4fr_1fr]">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <span class="grid size-16 shrink-0 place-items-center rounded-2xl bg-brand-600 text-xl font-bold text-white">{{ strtoupper(substr($employee->full_name, 0, 2)) }}</span>
                <div class="min-w-0"><h2 class="text-xl font-semibold">{{ $employee->display_name }}</h2><p class="mt-1 text-sm text-slate-500">{{ $employee->position?->name ?? 'Jabatan belum ditentukan' }} · {{ $employee->department->name }}</p><p class="mt-2 text-sm text-slate-500">{{ $employee->email }} · {{ $employee->phone ?: 'Nomor HP belum diisi' }}</p></div>
            </div>
            <dl class="mt-6 grid gap-4 border-t border-slate-100 pt-5 sm:grid-cols-3">
                <div><dt class="text-xs uppercase tracking-wide text-slate-400">NIP</dt><dd class="mt-1 text-sm font-semibold">{{ $employee->nip ?: $employee->employee_number }}</dd></div>
                @if (auth()->user()->role === 'dosen')<div><dt class="text-xs uppercase tracking-wide text-slate-400">NIDN / Kode Dosen</dt><dd class="mt-1 text-sm font-semibold">{{ $employee->nidn ?: $employee->pas_kode_dosen ?: '-' }}</dd></div>@endif
                <div><dt class="text-xs uppercase tracking-wide text-slate-400">Atasan Langsung</dt><dd class="mt-1 text-sm font-semibold">{{ $employee->supervisor?->display_name ?? 'Belum ditentukan' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-slate-400">Masa Kerja</dt><dd class="mt-1 text-sm font-semibold">{{ $employee->length_of_service }}</dd></div>
            </dl>
        </article>

        @php
            $usedLeave = (int) $employee->approved_leave_days;
            $remainingLeave = max(0, $employee->annual_leave_days - $usedLeave);
        @endphp
        <article id="cuti" class="rounded-2xl bg-slate-900 p-6 text-white shadow-sm">
            <div class="flex items-start justify-between"><div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-100">Saldo cuti {{ now()->year }}</p><p class="mt-3 text-4xl font-semibold">{{ $remainingLeave }} <span class="text-base font-normal text-slate-400">hari</span></p></div><span class="rounded-xl bg-white/10 px-3 py-2 text-xs">Jatah {{ $employee->annual_leave_days }}</span></div>
            <div class="mt-6 flex items-center justify-between border-t border-white/10 pt-4 text-sm"><span class="text-slate-400">Terpakai {{ $usedLeave }} hari</span><a href="{{ route('leave-requests.create') }}" class="rounded-xl bg-brand-500 px-4 py-2 font-semibold text-white">Ajukan Cuti</a></div>
        </article>
    </section>

    <section id="todo" class="mt-6 grid gap-6 xl:grid-cols-[1fr_1.35fr]">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Tambah Todo</h2><p class="mt-1 text-sm text-slate-500">Catat pekerjaan yang perlu Anda selesaikan.</p>
            <form method="POST" action="{{ route('todos.store') }}" class="mt-5 flex flex-col gap-4">@csrf
                <label class="text-sm font-semibold text-slate-600">Pekerjaan<input name="title" value="{{ old('title') }}" required maxlength="255" placeholder="Contoh: Menyiapkan laporan bulanan" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></label>
                <div class="grid gap-4 sm:grid-cols-2"><label class="text-sm font-semibold text-slate-600">Target selesai<input type="date" name="due_date" value="{{ old('due_date') }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm"></label><label class="text-sm font-semibold text-slate-600">Prioritas<select name="priority" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm"><option value="low">Rendah</option><option value="normal" selected>Normal</option><option value="high">Tinggi</option></select></label></div>
                <button class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white">+ Tambahkan Todo</button>
            </form>
        </article>

        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5"><h2 class="text-lg font-semibold">Todo Saya</h2><p class="mt-1 text-sm text-slate-500">{{ $todos->where('is_completed', false)->count() }} pekerjaan belum selesai</p></div>
            <div class="divide-y divide-slate-100">
                @forelse ($todos as $todo)
                    <div class="flex items-start gap-3 px-6 py-4 {{ $todo->is_completed ? 'bg-slate-50/70' : '' }}">
                        <form method="POST" action="{{ route('todos.update', $todo) }}">@csrf @method('PUT')<button class="mt-0.5 grid size-6 place-items-center rounded-full border {{ $todo->is_completed ? 'border-brand-500 bg-brand-500 text-white' : 'border-slate-300 text-transparent' }}" title="Ubah status">✓</button></form>
                        <div class="min-w-0 flex-1"><p class="text-sm font-medium {{ $todo->is_completed ? 'text-slate-400 line-through' : '' }}">{{ $todo->title }}</p><div class="mt-2 flex flex-wrap gap-2 text-xs"><span class="rounded-full px-2 py-1 {{ $todo->priority === 'high' ? 'bg-red-50 text-red-700' : ($todo->priority === 'low' ? 'bg-slate-100 text-slate-500' : 'bg-amber-50 text-amber-700') }}">{{ ['low' => 'Rendah', 'normal' => 'Normal', 'high' => 'Tinggi'][$todo->priority] }}</span>@if ($todo->due_date)<span class="px-1 py-1 text-slate-400">Target {{ $todo->due_date->format('d/m/Y') }}</span>@endif</div></div>
                        <form method="POST" action="{{ route('todos.destroy', $todo) }}" onsubmit="return confirm('Hapus todo ini?')">@csrf @method('DELETE')<button class="text-xs font-semibold text-red-500">Hapus</button></form>
                    </div>
                @empty
                    <p class="px-6 py-12 text-center text-sm text-slate-400">Belum ada todo. Tambahkan pekerjaan pertama Anda.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section id="laporan" class="mt-6 grid gap-6 xl:grid-cols-[1fr_1.35fr]">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Input Laporan Pekerjaan</h2><p class="mt-1 text-sm text-slate-500">Simpan progres atau pekerjaan yang telah selesai.</p>
            <form method="POST" action="{{ route('work-reports.store') }}" class="mt-5 flex flex-col gap-4">@csrf
                <div class="grid gap-4 sm:grid-cols-2"><label class="text-sm font-semibold text-slate-600">Tanggal<input type="date" name="report_date" value="{{ old('report_date', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm"></label><label class="text-sm font-semibold text-slate-600">Status<select name="status" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm"><option value="completed">Selesai</option><option value="progress">Dalam Proses</option></select></label></div>
                <label class="text-sm font-semibold text-slate-600">Judul<input name="title" value="{{ old('title') }}" required maxlength="255" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm"></label>
                <label class="text-sm font-semibold text-slate-600">Uraian<textarea name="description" rows="4" required maxlength="5000" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm" placeholder="Jelaskan hasil pekerjaan...">{{ old('description') }}</textarea></label>
                <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white">Simpan Laporan</button>
            </form>
        </article>

        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5"><h2 class="text-lg font-semibold">Laporan Terbaru</h2><p class="mt-1 text-sm text-slate-500">Lima laporan pekerjaan terakhir</p></div>
            <div class="divide-y divide-slate-100">
                @forelse ($workReports as $report)
                    <div class="px-6 py-4"><div class="flex items-start justify-between gap-4"><div><div class="flex flex-wrap items-center gap-2"><h3 class="text-sm font-semibold">{{ $report->title }}</h3><span class="rounded-full px-2 py-1 text-[11px] font-semibold {{ $report->status === 'completed' ? 'bg-brand-50 text-brand-700' : 'bg-amber-50 text-amber-700' }}">{{ $report->status === 'completed' ? 'Selesai' : 'Dalam Proses' }}</span></div><p class="mt-1 text-xs text-slate-400">{{ $report->report_date->locale('id')->translatedFormat('d F Y') }}</p></div><form method="POST" action="{{ route('work-reports.destroy', $report) }}" onsubmit="return confirm('Hapus laporan ini?')">@csrf @method('DELETE')<button class="text-xs font-semibold text-red-500">Hapus</button></form></div><p class="mt-3 text-sm leading-6 text-slate-600">{{ $report->description }}</p></div>
                @empty
                    <p class="px-6 py-12 text-center text-sm text-slate-400">Belum ada laporan pekerjaan.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section class="mt-6 grid gap-6 {{ auth()->user()->role === 'dosen' ? 'xl:grid-cols-2' : '' }}">
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5"><div><h2 class="font-semibold">Riwayat Pengajuan Cuti</h2><p class="mt-1 text-sm text-slate-500">Lima pengajuan terakhir</p></div><a href="{{ route('leave-requests.index') }}" class="text-sm font-semibold text-brand-700">Lihat semua</a></div>
            <div class="divide-y divide-slate-100">@forelse ($leaveRequests as $leaveRequest)<a href="{{ route('leave-requests.show', $leaveRequest) }}" class="flex items-center justify-between gap-4 px-6 py-4 hover:bg-slate-50"><div><p class="text-sm font-semibold">{{ $leaveRequest->leave_type->label() }} · {{ $leaveRequest->start_date->format('d/m/Y') }}</p><p class="mt-1 text-xs text-slate-400">{{ $leaveRequest->durationLabel() }} · {{ $leaveRequest->reason }}</p></div><span class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $leaveRequest->status->label() }}</span></a>@empty<p class="px-6 py-12 text-center text-sm text-slate-400">Belum ada pengajuan cuti atau izin.</p>@endforelse</div>
        </article>

        @if (auth()->user()->role === 'dosen')
            <article id="bap" class="overflow-hidden rounded-2xl border border-sky-200 bg-sky-50 shadow-sm">
                <div class="flex items-center justify-between border-b border-sky-100 px-6 py-5"><div><p class="text-xs font-semibold uppercase tracking-wider text-sky-700">Terhubung dengan PAS</p><h2 class="mt-1 font-semibold">Laporan BAP Dosen</h2><p class="mt-1 text-xs text-sky-700">{{ $bapSummary['academic_year'] ?? 'Tahun akademik aktif' }}</p></div><a href="{{ route('lecturer-bap.index') }}" class="text-sm font-semibold text-sky-800">Buka BAP</a></div>
                @if ($bapSummary['connected'])
                    <div class="grid grid-cols-3 gap-3 p-5"><div class="rounded-xl bg-white p-3 text-center"><p class="text-2xl font-semibold">{{ $bapSummary['theory'] + $bapSummary['practice'] }}</p><p class="mt-1 text-xs text-slate-400">Pertemuan</p></div><div class="rounded-xl bg-white p-3 text-center"><p class="text-2xl font-semibold">{{ $bapSummary['theory'] }}</p><p class="mt-1 text-xs text-slate-400">Teori</p></div><div class="rounded-xl bg-white p-3 text-center"><p class="text-2xl font-semibold">{{ $bapSummary['practice'] }}</p><p class="mt-1 text-xs text-slate-400">Praktik</p></div></div>
                    <div class="divide-y divide-sky-100 border-t border-sky-100">@forelse ($bapSummary['meetings'] as $meeting)<div class="px-6 py-3"><div class="flex items-center justify-between gap-3"><p class="text-sm font-semibold">{{ $meeting->mata_kuliah }}</p><span class="text-xs text-sky-700">{{ $meeting->jenis }}</span></div><p class="mt-1 text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($meeting->tanggal_pertemuan)->format('d/m/Y') }} · {{ $meeting->topik ?: 'Topik belum diisi' }}</p></div>@empty<p class="px-6 py-8 text-center text-sm text-sky-700">Belum ada BAP pada semester aktif.</p>@endforelse</div>
                @else
                    <div class="p-6 text-sm leading-6 text-sky-800">Data BAP belum dapat dimuat. Pastikan identitas dosen sudah tersinkron dan koneksi PAS aktif.</div>
                @endif
            </article>
        @endif
    </section>
@endsection
