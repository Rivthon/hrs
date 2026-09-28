@extends('layouts.app')
@section('title', 'Master Departemen | HRS Kampus')
@section('content')
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
        <div><p class="text-sm font-semibold text-brand-600">Master organisasi</p><h1 class="mt-1 text-3xl font-semibold tracking-tight">Master Departemen</h1><p class="mt-2 text-sm text-slate-500">Kelola unit induk, unit bawahan, dan status departemen kampus.</p></div>
        <a href="{{ route('departments.create') }}" class="rounded-xl bg-brand-600 px-5 py-2.5 text-center text-sm font-semibold text-white">+ Tambah Departemen</a>
    </div>

    <form method="GET" class="mt-6 flex max-w-xl gap-2"><input name="search" value="{{ $search }}" placeholder="Cari kode atau nama departemen..." class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"><button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white">Cari</button></form>

    <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto"><table class="w-full min-w-[60rem] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">Kode / Departemen</th><th class="px-5 py-3">Unit Induk</th><th class="px-5 py-3">Jenis</th><th class="px-5 py-3">Pegawai</th><th class="px-5 py-3">Unit Bawahan</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Aksi</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse ($departments as $department)
                <tr class="hover:bg-slate-50/70"><td class="px-5 py-4"><p class="font-semibold">{{ $department->name }}</p><p class="mt-1 font-mono text-xs text-slate-400">{{ $department->code }}</p></td><td class="px-5 py-4 text-slate-600">{{ $department->parent?->name ?? 'Unit utama' }}</td><td class="px-5 py-4"><span class="rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700">{{ str($department->type)->replace('_', ' ')->title() }}</span></td><td class="px-5 py-4">{{ $department->employees_count }}</td><td class="px-5 py-4">{{ $department->children_count }}</td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $department->is_active ? 'bg-brand-50 text-brand-700' : 'bg-slate-100 text-slate-500' }}">{{ $department->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td class="px-5 py-4"><div class="flex justify-end gap-3"><a href="{{ route('departments.edit', $department) }}" class="font-semibold text-brand-700">Edit</a><form method="POST" action="{{ route('departments.destroy', $department) }}" onsubmit="return confirm('Hapus departemen ini?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Hapus</button></form></div></td></tr>
            @empty
                <tr><td colspan="7" class="px-5 py-12 text-center text-slate-400">Departemen tidak ditemukan.</td></tr>
            @endforelse
        </tbody></table></div><div class="border-t border-slate-100 px-5 py-4">{{ $departments->links() }}</div>
    </div>
@endsection
