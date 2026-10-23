@extends('layouts.app')

@section('title', 'Beranda · Bara Cuci Kinclong')

@section('content')
    <h1 class="text-2xl font-bold">Halo, {{ auth()->user()->name }} 👋</h1>
    <p class="text-sm text-slate-500">
        Peran Anda: {{ auth()->user()->role?->getLabel() }}
    </p>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-5 text-sm text-slate-600">
        Login berhasil. Fitur booking menyusul di fase berikutnya.
    </div>
@endsection
