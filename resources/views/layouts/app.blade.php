<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Human Resource System Kampus">
    <title>@yield('title', 'HRS Kampus')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
        <aside class="hidden border-r border-slate-200 bg-white px-5 py-7 lg:flex lg:flex-col">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-2">
                <span class="grid size-11 place-items-center rounded-2xl bg-brand-600 font-bold text-white">HR</span>
                <span><strong class="block text-lg tracking-tight">HRS Kampus</strong><small class="text-slate-500">Human Resource System</small></span>
            </a>
            <nav class="mt-10 flex flex-col gap-1 text-sm font-medium">
                <a href="{{ route('dashboard') }}" class="rounded-xl px-4 py-3 {{ request()->routeIs('dashboard') ? 'bg-brand-50 text-brand-700' : 'text-slate-500 hover:bg-slate-50' }}">Dashboard</a>
                <a href="{{ route('employees.index') }}" class="rounded-xl px-4 py-3 {{ request()->routeIs('employees.*') ? 'bg-brand-50 text-brand-700' : 'text-slate-500 hover:bg-slate-50' }}">Manajemen User</a>
                @can('manage-users')<a href="{{ route('departments.index') }}" class="rounded-xl px-4 py-3 {{ request()->routeIs('departments.*') ? 'bg-brand-50 text-brand-700' : 'text-slate-500 hover:bg-slate-50' }}">Master Departemen</a>@endcan
                <a href="{{ route('leave-requests.index') }}" class="rounded-xl px-4 py-3 {{ request()->routeIs('leave-requests.*') ? 'bg-brand-50 text-brand-700' : 'text-slate-500 hover:bg-slate-50' }}">Cuti & Izin</a>
                @foreach (['Kehadiran', 'Penggajian'] as $menu)
                    <span class="cursor-not-allowed rounded-xl px-4 py-3 text-slate-300">{{ $menu }}</span>
                @endforeach
            </nav>
            <div class="mt-auto rounded-2xl bg-slate-900 p-4 text-sm text-slate-300">Kelola data SDM kampus secara terpusat.</div>
        </aside>
        <main class="min-w-0">
            <header class="flex items-center justify-between border-b border-slate-200 bg-white px-5 py-4 md:px-8">
                <div class="flex items-center gap-3"><span class="grid size-9 place-items-center rounded-xl bg-brand-600 text-xs font-bold text-white lg:hidden">HR</span><span class="font-semibold lg:hidden">HRS Kampus</span></div>
                <div class="ml-auto flex items-center gap-3 text-right"><div><p class="text-sm font-semibold">{{ auth()->user()?->name ?? 'Administrator' }}</p><p class="text-xs uppercase text-slate-400">{{ auth()->user()?->role ?? 'Admin SDM' }}</p></div><span class="grid size-10 place-items-center rounded-full bg-amber-100 font-semibold text-amber-800">{{ strtoupper(substr(auth()->user()?->name ?? 'AD', 0, 2)) }}</span>@auth<form method="POST" action="{{ route('logout') }}">@csrf<button class="text-xs font-semibold text-slate-500 hover:text-red-600">Keluar</button></form>@endauth</div>
            </header>
            <div class="mx-auto max-w-7xl px-5 py-8 md:px-8 lg:px-10">
                @if (session('success'))
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
