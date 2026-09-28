@extends('layouts.app')
@section('title', 'Tambah User | HRS Kampus')
@section('content')
    <div><a href="{{ route('employees.index') }}" class="text-sm font-semibold text-brand-700">← Kembali</a><h1 class="mt-3 text-3xl font-semibold">Tambah User</h1><p class="mt-2 text-sm text-slate-500">NIP akan menjadi password awal dan pengguna wajib menggantinya.</p></div>
    <form method="POST" action="{{ route('employees.store') }}" class="mt-6">@csrf @include('employees._form', ['employee' => null])</form>
@endsection
