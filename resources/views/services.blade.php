@extends('layouts.app')

@section('title', 'Layanan · Bara Cuci Kinclong')

@section('content')
    <h1 class="text-2xl font-bold">Daftar layanan</h1>
    <p class="text-sm text-slate-500">Harga bersifat informasi. Pembayaran dilakukan langsung di lokasi.</p>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        @forelse ($services as $service)
            <div class="flex flex-col rounded-xl border border-slate-200 bg-white p-5">
                <h2 class="text-lg font-semibold">{{ $service->name }}</h2>
                <p class="mt-1 flex-1 text-sm text-slate-500">{{ $service->description }}</p>
                <div class="mt-4">
                    <p class="text-xl font-bold text-sky-600">{{ $service->priceLabel() }}</p>
                    <p class="text-xs text-slate-400">± {{ $service->duration_minutes }} menit</p>
                </div>
                {{-- FASE 6 menambahkan tombol "Booking" di sini. --}}
            </div>
        @empty
            <p class="text-slate-500">Belum ada layanan.</p>
        @endforelse
    </div>
@endsection
