<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Human Resource System STIKes Bogor Husada untuk pengelolaan SDM kampus.">
    <title>HRS Kampus | STIKes Bogor Husada</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white">
    <div class="relative isolate overflow-hidden">
        <div class="absolute inset-x-0 top-0 -z-10 h-[42rem] bg-[radial-gradient(circle_at_top_right,_#d7f7ea_0,_transparent_40%),linear-gradient(180deg,#f7fffb_0%,#ffffff_100%)]"></div>

        <header class="mx-auto flex max-w-7xl items-center justify-between px-5 py-5 md:px-8 lg:px-10">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="HRS Kampus - Beranda">
                <img src="{{ asset('images/12.png') }}" alt="Logo STIKes Bogor Husada" class="h-14 w-auto md:h-16">
                <span class="hidden border-l border-slate-200 pl-3 sm:block"><strong class="block text-base text-slate-900">HRS Kampus</strong><small class="text-xs text-slate-500">Human Resource System</small></span>
            </a>
            <nav class="flex items-center gap-3" aria-label="Navigasi utama">
                <a href="#fitur" class="hidden text-sm font-semibold text-slate-600 hover:text-brand-700 sm:block">Fitur</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/20 hover:bg-brand-700">Buka Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/20 hover:bg-brand-700">Masuk HRS</a>
                @endauth
            </nav>
        </header>

        <main>
            <section class="mx-auto grid max-w-7xl items-center gap-12 px-5 pb-20 pt-14 md:px-8 md:pt-20 lg:grid-cols-[1.05fr_.95fr] lg:px-10 lg:pb-28">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-brand-100 bg-brand-50 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.16em] text-brand-700"><span class="size-2 rounded-full bg-brand-500"></span> Portal SDM Terintegrasi</span>
                    <h1 class="mt-6 max-w-3xl text-4xl font-semibold leading-tight tracking-tight text-slate-950 md:text-6xl">Kelola SDM kampus dalam <span class="text-brand-600">satu sistem.</span></h1>
                    <p class="mt-6 max-w-2xl text-base leading-8 text-slate-600 md:text-lg">HRS Kampus membantu STIKes Bogor Husada mengelola data karyawan, pengajuan cuti, struktur organisasi, dan payroll dengan alur yang rapi dan mudah dipantau.</p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-xl bg-brand-600 px-6 py-3.5 text-center text-sm font-semibold text-white shadow-xl shadow-brand-600/20 hover:bg-brand-700">Masuk ke Dashboard <span aria-hidden="true">→</span></a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-xl bg-brand-600 px-6 py-3.5 text-center text-sm font-semibold text-white shadow-xl shadow-brand-600/20 hover:bg-brand-700">Masuk ke Akun <span aria-hidden="true">→</span></a>
                        @endauth
                        <a href="#fitur" class="rounded-xl border border-slate-200 bg-white px-6 py-3.5 text-center text-sm font-semibold text-slate-700 shadow-sm hover:border-brand-200 hover:text-brand-700">Lihat Fitur</a>
                    </div>
                    <div class="mt-10 flex flex-wrap gap-x-8 gap-y-3 text-sm text-slate-500">
                        <span class="flex items-center gap-2"><span class="grid size-5 place-items-center rounded-full bg-brand-100 text-xs text-brand-700">✓</span> Data terpusat</span>
                        <span class="flex items-center gap-2"><span class="grid size-5 place-items-center rounded-full bg-brand-100 text-xs text-brand-700">✓</span> Alur persetujuan jelas</span>
                        <span class="flex items-center gap-2"><span class="grid size-5 place-items-center rounded-full bg-brand-100 text-xs text-brand-700">✓</span> Slip gaji digital</span>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-xl">
                    <div class="absolute -inset-5 -z-10 rounded-[2rem] bg-brand-100/60 blur-2xl"></div>
                    <div class="overflow-hidden rounded-[2rem] border border-white/80 bg-slate-950 p-3 shadow-2xl shadow-slate-900/20">
                        <div class="rounded-[1.5rem] bg-white p-5 md:p-7">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-5"><div><p class="text-xs font-semibold uppercase tracking-wider text-brand-600">HRS Kampus</p><p class="mt-1 text-lg font-semibold text-slate-900">Ringkasan SDM</p></div><span class="grid size-10 place-items-center rounded-full bg-brand-50 text-xs font-bold text-brand-700">SBH</span></div>
                            <div class="mt-5 grid grid-cols-2 gap-3">
                                @foreach ([['Pegawai Aktif', 'SDM', 'bg-brand-50 text-brand-700'], ['Pengajuan Cuti', 'Online', 'bg-amber-50 text-amber-700'], ['Payroll', 'Bulanan', 'bg-blue-50 text-blue-700'], ['Slip Gaji', 'Digital', 'bg-violet-50 text-violet-700']] as [$label, $value, $color])
                                    <div class="rounded-2xl border border-slate-100 p-4"><span class="inline-flex rounded-lg px-2 py-1 text-[10px] font-bold uppercase {{ $color }}">{{ $value }}</span><p class="mt-5 text-sm font-semibold text-slate-800">{{ $label }}</p><div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full w-3/4 rounded-full bg-brand-500"></div></div></div>
                                @endforeach
                            </div>
                            <div class="mt-4 rounded-2xl bg-slate-50 p-4"><div class="flex items-center justify-between"><span class="text-xs font-semibold text-slate-500">Alur persetujuan cuti</span><span class="text-xs font-bold text-brand-700">Terpantau</span></div><div class="mt-4 flex items-center"><span class="grid size-8 place-items-center rounded-full bg-brand-600 text-xs font-bold text-white">1</span><span class="h-0.5 flex-1 bg-brand-200"></span><span class="grid size-8 place-items-center rounded-full bg-brand-600 text-xs font-bold text-white">2</span><span class="h-0.5 flex-1 bg-brand-200"></span><span class="grid size-8 place-items-center rounded-full bg-brand-600 text-xs font-bold text-white">3</span></div><div class="mt-2 grid grid-cols-3 text-center text-[10px] text-slate-500"><span>Pengajuan</span><span>Atasan</span><span>SDM</span></div></div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="fitur" class="border-y border-slate-100 bg-slate-50/70 py-20">
                <div class="mx-auto max-w-7xl px-5 md:px-8 lg:px-10">
                    <div class="max-w-2xl"><p class="text-sm font-semibold uppercase tracking-[0.16em] text-brand-600">Fitur Utama</p><h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 md:text-4xl">Pekerjaan SDM menjadi lebih sederhana</h2><p class="mt-4 leading-7 text-slate-600">Seluruh proses penting tersedia dalam satu portal yang aman bagi karyawan, atasan, dan tim SDM.</p></div>
                    <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ([
                            ['01', 'Manajemen Karyawan', 'Simpan profil, jabatan, departemen, pendidikan, serta data kepegawaian secara terpusat.'],
                            ['02', 'Cuti Berjenjang', 'Pengajuan cuti terhubung dengan pengganti, atasan langsung, dan persetujuan SDM.'],
                            ['03', 'Payroll & Slip Gaji', 'Hitung pendapatan dan potongan, unduh slip PDF, lalu kirimkan melalui email.'],
                        ] as [$number, $title, $description])
                            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-brand-200 hover:shadow-lg"><span class="text-xs font-bold tracking-[0.2em] text-brand-600">{{ $number }}</span><h3 class="mt-8 text-xl font-semibold text-slate-900">{{ $title }}</h3><p class="mt-3 text-sm leading-7 text-slate-500">{{ $description }}</p></article>
                        @endforeach
                    </div>
                </div>
            </section>
        </main>

        <footer class="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-8 text-sm text-slate-500 md:flex-row md:items-center md:justify-between md:px-8 lg:px-10"><p>© {{ now()->year }} STIKes Bogor Husada. Seluruh hak dilindungi.</p><p>Human Resource System Kampus</p></footer>
    </div>
</body>
</html>
