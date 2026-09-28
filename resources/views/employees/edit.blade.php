@extends('layouts.app')
@section('title', 'Edit User | HRS Kampus')
@section('content')
    <div><a href="{{ route('employees.show', $employee) }}" class="text-sm font-semibold text-brand-700">← Kembali ke detail</a><h1 class="mt-3 text-3xl font-semibold">Edit User</h1><p class="mt-2 text-sm text-slate-500">Perbarui profil {{ $employee->display_name }}.</p></div>
    <form method="POST" action="{{ route('employees.update', $employee) }}" class="mt-6">@csrf @method('PUT') @include('employees._form')</form>
@endsection
