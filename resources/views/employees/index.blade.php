@extends('layouts.app')

@section('title', 'Manajemen User | HRS Kampus')

@section('content')
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
        <div><p class="text-sm font-semibold text-brand-600">Master data</p><h1 class="mt-1 text-3xl font-semibold tracking-tight">Manajemen User</h1><p class="mt-2 text-sm text-slate-500">Kelola akun dan profil lengkap seluruh pegawai kampus.</p></div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('employees.export') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700">Export CSV</a>
            <a href="{{ route('employees.create') }}" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm">+ Tambah User</a>
        </div>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-[1fr_auto]">
        <form method="GET" class="flex gap-2">
            <input name="search" value="{{ $search }}" placeholder="Cari nama, NIP, atau email..." class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white">Cari</button>
        </form>
        <form method="POST" action="{{ route('employees.import') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
            @csrf
            <a href="{{ route('employees.import-template') }}" class="text-xs font-semibold text-brand-700">Unduh template per kolom</a>
            <input type="file" name="file" accept=".csv,.txt" required class="max-w-56 rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs">
            <button class="rounded-xl border border-brand-600 px-4 py-2 text-sm font-semibold text-brand-700">Import</button>
        </form>
    </div>
    @error('file')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

    <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[60rem] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">Pegawai</th><th class="px-5 py-3">NIP</th><th class="px-5 py-3">Departemen / Jabatan</th><th class="px-5 py-3">Role</th><th class="px-5 py-3">Masa kerja</th><th class="px-5 py-3 text-right">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($employees as $employee)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-4"><p class="font-semibold">{{ $employee->display_name }}</p><p class="mt-1 text-xs text-slate-400">{{ $employee->email }}</p></td>
                            <td class="px-5 py-4 text-slate-600">{{ $employee->nip ?? $employee->employee_number }}</td>
                            <td class="px-5 py-4"><p>{{ $employee->department->name }}</p><p class="mt-1 text-xs text-slate-400">{{ $employee->position?->name ?? '-' }}</p></td>
                            <td class="px-5 py-4"><span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold uppercase text-brand-700">{{ $employee->user?->role ?? 'belum ada akun' }}</span></td>
                            <td class="px-5 py-4 text-slate-600">{{ $employee->length_of_service }}</td>
                            <td class="px-5 py-4 text-right"><a href="{{ route('employees.show', $employee) }}" class="font-semibold text-brand-700">Detail</a><a href="{{ route('employees.edit', $employee) }}" class="ml-4 font-semibold text-slate-600">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400">Data pengguna tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $employees->links() }}</div>
    </div>
@endsection
