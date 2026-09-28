<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Login | HRS Kampus</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body>
    <main class="grid min-h-screen place-items-center px-5 py-10">
        <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-7 shadow-xl shadow-slate-200/60 md:p-9">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3"><span class="grid size-11 place-items-center rounded-2xl bg-brand-600 font-bold text-white">HR</span><span><strong class="block text-lg">HRS Kampus</strong><small class="text-slate-400">Portal SDM Kampus</small></span></a>
            <h1 class="mt-8 text-2xl font-semibold">Masuk ke akun</h1><p class="mt-2 text-sm text-slate-500">Gunakan email aktif dan password akun Anda.</p>
            <form method="POST" action="{{ route('login') }}" class="mt-7 flex flex-col gap-5">@csrf
                <label class="text-sm font-semibold">Email<input type="email" name="email" value="{{ old('email') }}" required autofocus class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">@error('email')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
                <label class="text-sm font-semibold">Password<input type="password" name="password" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></label>
                <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remember" value="1" class="size-4 rounded"> Ingat saya</label>
                <button class="rounded-xl bg-brand-600 px-5 py-3 font-semibold text-white">Masuk</button>
            </form>
        </div>
    </main>
</body>
</html>
