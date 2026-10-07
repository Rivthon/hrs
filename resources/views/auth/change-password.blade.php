@extends('layouts.app')
@section('title', 'Ganti Password | HRS Kampus')
@section('content')
    <div class="mx-auto max-w-xl">
        <section class="rounded-2xl border border-amber-200 bg-white p-6 shadow-sm md:p-8">
            <p class="text-sm font-semibold text-amber-700">Keamanan akun</p>
            <h1 class="mt-2 text-3xl font-semibold">Buat password baru</h1>
            <p class="mt-3 text-sm leading-6 text-slate-500">Password sementara harus diganti sebelum Anda dapat menggunakan HRS. Gunakan minimal 12 karakter yang berisi huruf dan angka.</p>
            <form method="POST" action="{{ route('password.update') }}" class="mt-7 space-y-5">
                @csrf
                @method('PUT')
                <label class="block text-sm font-medium text-slate-700">Password sementara
                    <input type="password" name="current_password" required autocomplete="current-password" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                </label>
                <label class="block text-sm font-medium text-slate-700">Password baru
                    <input type="password" name="password" required autocomplete="new-password" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                </label>
                <label class="block text-sm font-medium text-slate-700">Ulangi password baru
                    <input type="password" name="password_confirmation" required autocomplete="new-password" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                </label>
                <button class="w-full rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Simpan Password Baru</button>
            </form>
        </section>
    </div>
@endsection
