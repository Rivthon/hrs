<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard Human Resource System Kampus">
    <title>Dashboard | HRS Kampus</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
        <aside class="hidden border-r border-slate-200 bg-white px-5 py-7 lg:flex lg:flex-col">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-2" aria-label="HRS Kampus">
                <span class="grid size-11 place-items-center rounded-2xl bg-brand-600 text-white shadow-lg shadow-brand-600/20">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="m3 10 9-5 9 5-9 5-9-5Z"/><path d="M7 12.2V17c2.8 2 7.2 2 10 0v-4.8M21 10v6"/>
                    </svg>
                </span>
                <span><strong class="block text-lg tracking-tight">HRS Kampus</strong><small class="text-slate-500">Human Resource System</small></span>
            </a>

            <nav class="mt-10 flex flex-col gap-1 text-sm font-medium">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl bg-brand-50 px-4 py-3 text-brand-700">
                    <span class="size-2 rounded-full bg-brand-500"></span> Dashboard
                </a>
                <a href="{{ route('employees.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-500 hover:bg-slate-50">
                    <span class="size-2 rounded-full border border-slate-300"></span> Manajemen User
                </a>
                @auth @can('manage-users')
                    <a href="{{ route('departments.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-500 hover:bg-slate-50">
                        <span class="size-2 rounded-full border border-slate-300"></span> Master Departemen
                    </a>
                @endcan @endauth
                <a href="{{ route('leave-requests.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-500 hover:bg-slate-50">
                    <span class="size-2 rounded-full border border-slate-300"></span> Cuti & Izin
                </a>
                @auth @can('manage-users')<a href="{{ route('payroll-periods.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-500 hover:bg-slate-50"><span class="size-2 rounded-full border border-slate-300"></span> Payroll</a>@endcan @endauth
                @foreach (['Jabatan', 'Kehadiran'] as $menu)
                    <span class="flex cursor-not-allowed items-center gap-3 rounded-xl px-4 py-3 text-slate-400" title="Segera hadir">
                        <span class="size-2 rounded-full border border-slate-300"></span> {{ $menu }}
                    </span>
                @endforeach
            </nav>

            <div class="mt-auto rounded-2xl bg-slate-900 p-4 text-white">
                <p class="text-xs font-semibold uppercase tracking-widest text-brand-100">Tahap awal</p>
                <p class="mt-2 text-sm leading-6 text-slate-300">Data master SDM sudah siap untuk dikembangkan ke modul operasional.</p>
            </div>
        </aside>

        <main>
            <header class="flex items-center justify-between border-b border-slate-200 bg-white/90 px-5 py-4 backdrop-blur md:px-8 lg:px-10">
                <div class="flex items-center gap-3 lg:hidden">
                    <span class="grid size-10 place-items-center rounded-xl bg-brand-600 text-sm font-bold text-white">HR</span>
                    <strong>HRS Kampus</strong>
                </div>
                <p class="hidden text-sm text-slate-500 lg:block">Sistem Informasi Sumber Daya Manusia</p>
                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold">Administrator</p>
                        <p class="text-xs text-slate-500">Admin SDM</p>
                    </div>
                    <span class="grid size-10 place-items-center rounded-full bg-amber-100 font-semibold text-amber-800">AD</span>
                </div>
            </header>

            <div class="mx-auto max-w-7xl px-5 py-8 md:px-8 lg:px-10 lg:py-10">
                <section class="flex flex-col justify-between gap-5 md:flex-row md:items-end">
                    <div>
                        <p class="text-sm font-semibold text-brand-600">Dashboard utama</p>
                        <h1 class="mt-1 text-3xl font-semibold tracking-tight md:text-4xl">Ringkasan SDM Kampus</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Pantau data pegawai dan struktur organisasi kampus dari satu tempat.</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-500 shadow-sm">
                        Waktu sistem {{ now()->format('d/m/Y H.i') }}
                    </div>
                </section>

                <section class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @php
                        $cards = [
                            ['label' => 'Total Pegawai', 'value' => $statistics['employees'], 'tone' => 'bg-brand-50 text-brand-700', 'initial' => 'PG'],
                            ['label' => 'Pegawai Aktif', 'value' => $statistics['active_employees'], 'tone' => 'bg-sky-50 text-sky-700', 'initial' => 'AK'],
                            ['label' => 'Unit Kerja Aktif', 'value' => $statistics['departments'], 'tone' => 'bg-violet-50 text-violet-700', 'initial' => 'UK'],
                            ['label' => 'Jabatan Aktif', 'value' => $statistics['positions'], 'tone' => 'bg-amber-50 text-amber-700', 'initial' => 'JB'],
                        ];
                    @endphp
                    @foreach ($cards as $card)
                        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm text-slate-500">{{ $card['label'] }}</p>
                                    <p class="mt-3 text-3xl font-semibold tracking-tight">{{ number_format($card['value']) }}</p>
                                </div>
                                <span class="grid size-11 place-items-center rounded-xl text-xs font-bold {{ $card['tone'] }}">{{ $card['initial'] }}</span>
                            </div>
                            <p class="mt-4 text-xs text-slate-400">Data tersinkron dengan basis data</p>
                        </article>
                    @endforeach
                </section>

                <section class="mt-6 grid gap-6 xl:grid-cols-[1.6fr_1fr]">
                    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5 md:px-6">
                            <div>
                                <h2 class="font-semibold">Pegawai terbaru</h2>
                                <p class="mt-1 text-sm text-slate-500">Lima data terakhir yang ditambahkan</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500">{{ $recentEmployees->count() }} data</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[38rem] text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                                    <tr><th class="px-6 py-3 font-medium">Pegawai</th><th class="px-6 py-3 font-medium">Unit Kerja</th><th class="px-6 py-3 font-medium">Jabatan</th><th class="px-6 py-3 font-medium">Status</th></tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($recentEmployees as $employee)
                                        <tr>
                                            <td class="px-6 py-4"><p class="font-medium">{{ $employee->full_name }}</p><p class="mt-1 text-xs text-slate-400">{{ $employee->employee_number }}</p></td>
                                            <td class="px-6 py-4 text-slate-600">{{ $employee->department->name }}</td>
                                            <td class="px-6 py-4 text-slate-600">{{ $employee->position?->name ?? 'Belum ditentukan' }}</td>
                                            <td class="px-6 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $employee->status === 'active' ? 'bg-brand-50 text-brand-700' : 'bg-slate-100 text-slate-500' }}">{{ $employee->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-6 py-12 text-center text-slate-400">Belum ada data pegawai. Jalankan seeder untuk melihat data contoh.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </article>

                    <aside class="rounded-2xl bg-slate-900 p-6 text-white shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-100">Fondasi sistem</p>
                        <h2 class="mt-3 text-xl font-semibold">Siap menuju tahap berikutnya</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-300">Struktur data utama sudah mencakup identitas pegawai, unit kerja, jabatan, status, dan jenis kepegawaian.</p>
                        <div class="mt-6 flex flex-col gap-3">
                            @foreach (['Manajemen data pegawai', 'Absensi dan jadwal kerja', 'Cuti dan persetujuan', 'Payroll dan laporan'] as $index => $feature)
                                <div class="flex items-center gap-3 rounded-xl bg-white/5 px-4 py-3">
                                    <span class="grid size-7 place-items-center rounded-full {{ $index === 0 ? 'bg-brand-500 text-white' : 'bg-white/10 text-slate-400' }} text-xs font-bold">{{ $index + 1 }}</span>
                                    <span class="text-sm {{ $index === 0 ? 'text-white' : 'text-slate-400' }}">{{ $feature }}</span>
                                </div>
                            @endforeach
                        </div>
                    </aside>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
