@extends('layouts.app')
@section('title', 'Edit Departemen | HRS Kampus')
@section('content')
    <a href="{{ route('departments.index') }}" class="text-sm font-semibold text-brand-700">← Master Departemen</a><h1 class="mt-3 text-3xl font-semibold">Edit Departemen</h1><p class="mt-2 text-sm text-slate-500">Perbarui {{ $department->name }} dan posisinya dalam struktur organisasi.</p>
    <form method="POST" action="{{ route('departments.update', $department) }}" class="mt-6">@csrf @method('PUT') @include('departments._form')</form>
@endsection
