@extends('layouts.app')
@section('title', 'Tambah Departemen | HRS Kampus')
@section('content')
    <a href="{{ route('departments.index') }}" class="text-sm font-semibold text-brand-700">← Master Departemen</a><h1 class="mt-3 text-3xl font-semibold">Tambah Departemen</h1><p class="mt-2 text-sm text-slate-500">Tambahkan unit utama atau unit bawahan ke struktur kampus.</p>
    <form method="POST" action="{{ route('departments.store') }}" class="mt-6">@csrf @include('departments._form', ['department' => null])</form>
@endsection
