@extends('layouts.app')

@section('title', 'Dashboard | HRS Kampus')

@section('content')
    @include('dashboard._leave-popup', ['employeesOnLeaveToday' => $employeesOnLeaveToday])
    @include('dashboard._supervised-business-trips', ['supervisedBusinessTrips' => $supervisedBusinessTrips])

    <section class="flex flex-col justify-between gap-5 md:flex-row md:items-end">
        <div>
            <p class="text-sm font-semibold text-brand-600">Dashboard admin</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight md:text-4xl">Ringkasan SDM Kampus</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Pantau data pegawai, pengajuan cuti, payroll, dan agenda SDM yang perlu ditindaklanjuti.</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-500 shadow-sm">Diperbarui {{ now()->locale('id')->translatedFormat('d F Y, H.i') }} WIB</div>
    </section>

    @can('manage-users')
    <section class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $cards = [
                ['label' => 'Total Pegawai', 'value' => $statistics['employees'], 'note' => $statistics['active_employees'].' pegawai aktif', 'tone' => 'bg-brand-50 text-brand-700', 'initial' => 'PG'],
                ['label' => 'Dosen', 'value' => $statistics['lecturers'], 'note' => 'Akun dengan role dosen', 'tone' => 'bg-sky-50 text-sky-700', 'initial' => 'DS'],
                ['label' => 'Tenaga Kependidikan', 'value' => $statistics['staff'], 'note' => 'Admin, SDM, dan staff', 'tone' => 'bg-violet-50 text-violet-700', 'initial' => 'TK'],
                ['label' => 'Perlu Persetujuan SDM', 'value' => $statistics['pending_hr_leave'], 'note' => 'Pengajuan cuti menunggu', 'tone' => 'bg-amber-50 text-amber-700', 'initial' => 'CT'],
            ];
        @endphp
        @foreach ($cards as $card)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4"><div><p class="text-sm text-slate-500">{{ $card['label'] }}</p><p class="mt-3 text-3xl font-semibold tracking-tight">{{ number_format($card['value'], 0, ',', '.') }}</p></div><span class="grid size-11 place-items-center rounded-xl text-xs font-bold {{ $card['tone'] }}">{{ $card['initial'] }}</span></div>
                <p class="mt-4 text-xs text-slate-400">{{ $card['note'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="mt-6 grid gap-4 md:grid-cols-3">
        <article class="rounded-2xl border border-amber-200 bg-amber-50 p-5"><div class="flex items-center justify-between gap-4"><p class="text-sm font-semibold text-amber-900">Menunggu Atasan</p><span class="rounded-full bg-white px-3 py-1 text-sm font-bold text-amber-700">{{ $statistics['pending_supervisor_leave'] }}</span></div><p class="mt-2 text-xs leading-5 text-amber-700">Pengajuan cuti belum diproses atasan langsung.</p></article>
        <article class="rounded-2xl border border-sky-200 bg-sky-50 p-5"><div class="flex items-center justify-between gap-4"><p class="text-sm font-semibold text-sky-900">Cuti Disetujui Bulan Ini</p><span class="rounded-full bg-white px-3 py-1 text-sm font-bold text-sky-700">{{ $statistics['approved_leave_this_month'] }}</span></div><p class="mt-2 text-xs leading-5 text-sky-700">Jumlah permohonan dengan persetujuan akhir SDM.</p></article>
        <article class="rounded-2xl border border-violet-200 bg-violet-50 p-5"><div class="flex items-center justify-between gap-4"><p class="text-sm font-semibold text-violet-900">Master Organisasi</p><span class="rounded-full bg-white px-3 py-1 text-sm font-bold text-violet-700">{{ $statistics['departments'] }} / {{ $statistics['positions'] }}</span></div><p class="mt-2 text-xs leading-5 text-violet-700">Unit kerja aktif / jabatan aktif.</p></article>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[1.45fr_1fr]">
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-5 md:px-6"><div><h2 class="font-semibold">Pengajuan Cuti Terbaru</h2><p class="mt-1 text-sm text-slate-500">Antrean yang masih membutuhkan persetujuan</p></div><a href="{{ route('leave-requests.index') }}" class="text-sm font-semibold text-brand-700">Lihat semua</a></div>
            <div class="divide-y divide-slate-100">
                @forelse ($pendingLeaveRequests as $leaveRequest)
                    <a href="{{ route('leave-requests.show', $leaveRequest) }}" class="flex flex-col justify-between gap-3 px-5 py-4 hover:bg-slate-50 sm:flex-row sm:items-center md:px-6">
                        <div><p class="font-medium">{{ $leaveRequest->employee->display_name }}</p><p class="mt-1 text-xs font-semibold text-brand-700">{{ $leaveRequest->leave_type->label() }}</p><p class="mt-1 text-xs text-slate-400">{{ $leaveRequest->employee->department->name }} · {{ $leaveRequest->start_date->format('d/m/Y') }}–{{ $leaveRequest->end_date->format('d/m/Y') }} · {{ $leaveRequest->durationLabel() }}</p></div>
                        <span @class(['w-fit rounded-full px-3 py-1 text-xs font-semibold', 'bg-amber-50 text-amber-700' => $leaveRequest->status === \App\Enums\LeaveRequestStatus::PendingSupervisor, 'bg-sky-50 text-sky-700' => $leaveRequest->status === \App\Enums\LeaveRequestStatus::PendingHr])>{{ $leaveRequest->status->label() }}</span>
                    </a>
                @empty
                    <div class="px-6 py-12 text-center"><p class="font-medium text-slate-600">Tidak ada antrean cuti</p><p class="mt-1 text-sm text-slate-400">Semua pengajuan sudah ditindaklanjuti.</p></div>
                @endforelse
            </div>
        </article>

        <div class="grid gap-6">
            <article class="rounded-2xl bg-slate-900 p-6 text-white shadow-sm">
                <div class="flex items-start justify-between gap-4"><div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-100">Payroll terbaru</p><h2 class="mt-2 text-xl font-semibold">{{ $latestPayrollPeriod?->period_date?->locale('id')->translatedFormat('F Y') ?? 'Belum tersedia' }}</h2></div>@if ($latestPayrollPeriod)<span class="rounded-full px-3 py-1 text-xs font-semibold uppercase {{ $latestPayrollPeriod->status === 'draft' ? 'bg-amber-400/15 text-amber-200' : 'bg-emerald-400/15 text-emerald-200' }}">{{ $latestPayrollPeriod->status === 'draft' ? 'Draft' : 'Final' }}</span>@endif</div>
                @if ($latestPayrollPeriod)
                    <p class="mt-6 text-sm text-slate-400">Total gaji bersih</p><p class="mt-1 text-2xl font-semibold">Rp {{ number_format((float) ($latestPayrollPeriod->payrolls_sum_net_salary ?? 0), 0, ',', '.') }}</p>
                    <div class="mt-5 flex items-center justify-between border-t border-white/10 pt-4 text-sm"><span class="text-slate-400">{{ $latestPayrollPeriod->payrolls_count }} slip gaji</span>@can('manage-users')<a href="{{ route('payroll-periods.show', $latestPayrollPeriod) }}" class="font-semibold text-brand-100">Buka payroll →</a>@endcan</div>
                @else
                    <p class="mt-4 text-sm leading-6 text-slate-300">Belum ada periode payroll yang dibuat.</p>
                @endif
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between"><div><h2 class="font-semibold">Tanggal Merah Mendatang</h2><p class="mt-1 text-sm text-slate-500">Kalender libur Indonesia</p></div>@can('manage-users')<a href="{{ route('public-holidays.index') }}" class="text-sm font-semibold text-brand-700">Kelola</a>@endcan</div>
                <div class="mt-5 flex flex-col gap-4">
                    @forelse ($upcomingHolidays as $holiday)
                        <div class="flex items-center gap-4"><span class="grid size-11 shrink-0 place-items-center rounded-xl bg-red-50 text-center text-xs font-bold leading-4 text-red-700">{{ $holiday->holiday_date->format('d') }}<br>{{ strtoupper($holiday->holiday_date->locale('id')->translatedFormat('M')) }}</span><div><p class="text-sm font-medium">{{ $holiday->name }}</p><p class="mt-1 text-xs text-slate-400">{{ $holiday->holiday_date->locale('id')->translatedFormat('l, d F Y') }}</p></div></div>
                    @empty
                        <p class="rounded-xl bg-slate-50 px-4 py-5 text-center text-sm text-slate-400">Belum ada tanggal merah mendatang.</p>
                    @endforelse
                </div>
            </article>
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[1fr_1.45fr]">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4"><div><h2 class="font-semibold">Sebaran Unit Kerja</h2><p class="mt-1 text-sm text-slate-500">Pegawai aktif per departemen</p></div>@can('manage-users')<a href="{{ route('departments.index') }}" class="text-sm font-semibold text-brand-700">Master</a>@endcan</div>
            <div class="mt-5 flex flex-col gap-4">
                @forelse ($departmentSummaries as $department)
                    @php $percentage = $statistics['active_employees'] > 0 ? min(100, round(($department->active_employees_count / $statistics['active_employees']) * 100)) : 0; @endphp
                    <div><div class="mb-2 flex items-center justify-between gap-3 text-sm"><span class="truncate font-medium">{{ $department->name }}</span><span class="shrink-0 text-slate-500">{{ $department->active_employees_count }} orang</span></div><div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand-500" style="width: {{ $percentage }}%"></div></div></div>
                @empty
                    <p class="rounded-xl bg-slate-50 px-4 py-5 text-center text-sm text-slate-400">Belum ada data unit kerja.</p>
                @endforelse
            </div>
        </article>

        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5 md:px-6"><div><h2 class="font-semibold">Pegawai Terbaru</h2><p class="mt-1 text-sm text-slate-500">Lima data terakhir yang ditambahkan</p></div>@can('manage-users')<a href="{{ route('employees.index') }}" class="text-sm font-semibold text-brand-700">Manajemen user</a>@endcan</div>
            <div class="overflow-x-auto"><table class="w-full min-w-[38rem] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-6 py-3 font-medium">Pegawai</th><th class="px-6 py-3 font-medium">Unit Kerja</th><th class="px-6 py-3 font-medium">Jabatan</th><th class="px-6 py-3 font-medium">Status</th></tr></thead><tbody class="divide-y divide-slate-100">
                @forelse ($recentEmployees as $employee)
                    <tr><td class="px-6 py-4"><p class="font-medium">{{ $employee->display_name }}</p><p class="mt-1 text-xs text-slate-400">{{ $employee->employee_number }}</p></td><td class="px-6 py-4 text-slate-600">{{ $employee->department->name }}</td><td class="px-6 py-4 text-slate-600">{{ $employee->position?->name ?? 'Belum ditentukan' }}</td><td class="px-6 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $employee->status === 'active' ? 'bg-brand-50 text-brand-700' : 'bg-slate-100 text-slate-500' }}">{{ $employee->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td></tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-12 text-center text-slate-400">Belum ada data pegawai.</td></tr>
                @endforelse
            </tbody></table></div>
        </article>
    </section>
    @else
        <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Selamat datang, {{ auth()->user()->name }}</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">Gunakan menu Cuti & Izin untuk melihat saldo, mengajukan cuti, dan memantau status persetujuan Anda.</p>
            <a href="{{ route('leave-requests.index') }}" class="mt-5 inline-flex rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white">Buka Cuti & Izin</a>
        </section>
    @endcan
@endsection
