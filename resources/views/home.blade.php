@extends('layouts.app')

@section('title', 'Beranda · Bara Cuci Kinclong')

@section('content')
    <h1 class="text-2xl font-bold">Halo, {{ auth()->user()->name }} 👋</h1>
    <p class="text-sm text-slate-500">Atur booking cuci kendaraan Anda dari sini.</p>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Booking aktif</p>
            <p class="text-2xl font-bold">{{ $upcoming->count() }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Cuci selesai</p>
            <p class="text-2xl font-bold">{{ $completedCount }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Kendaraan tersimpan</p>
            <p class="text-2xl font-bold">{{ $vehicleCount }}</p>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('bookings.create') }}" class="rounded-md bg-sky-600 px-5 py-2 text-white hover:bg-sky-700">Booking baru</a>
        <a href="{{ route('vehicles.index') }}" class="rounded-md border border-slate-300 bg-white px-5 py-2 hover:bg-slate-50">Kelola kendaraan</a>
    </div>

    <section class="mt-8">
        <h2 class="text-lg font-semibold">Booking berjalan</h2>
        <div class="mt-3 space-y-3">
            @forelse ($upcoming as $booking)
                <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white p-4">
                    <div>
                        <p class="font-medium">{{ $booking->service->name }}
                            <span class="text-xs text-slate-400">· {{ $booking->code }}</span></p>
                        <p class="text-sm text-slate-500">
                            {{ $booking->booking_date->translatedFormat('l, d M Y') }}, {{ $booking->timeLabel() }}
                            · {{ $booking->vehicle->plate_number }}
                        </p>
                    </div>
                    <x-status-badge :status="$booking->status" />
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada booking berjalan.</p>
            @endforelse
        </div>
    </section>
@endsection
