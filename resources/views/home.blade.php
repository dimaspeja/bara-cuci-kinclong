@extends('layouts.app')

@section('title', 'Beranda · Bara Cuci Kinclong')
@section('fullwidth', '1')

@section('content')
    @include('main.hero', ['eyebrow' => 'Halo, '.\Illuminate\Support\Str::before(auth()->user()->name, ' ').' · selamat datang di'])

    <section class="mx-auto max-w-6xl px-4 pt-10">
        <h2 class="text-2xl font-extrabold text-brand-dark">Ringkasan Akunmu</h2>
        <p class="text-sm text-slate-500">Atur booking cuci kendaraan Anda dari sini.</p>

        <div class="mt-5 grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-xs text-slate-500">Booking aktif</p>
                <p class="text-3xl font-extrabold text-brand-dark">{{ $upcoming->count() }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-xs text-slate-500">Cuci selesai</p>
                <p class="text-3xl font-extrabold text-brand-dark">{{ $completedCount }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-xs text-slate-500">Kendaraan tersimpan</p>
                <p class="text-3xl font-extrabold text-brand-dark">{{ $vehicleCount }}</p>
            </div>
        </div>

        <h3 class="mt-8 text-lg font-bold text-brand-dark">Booking berjalan</h3>
        <div class="mt-3 space-y-3">
            @forelse ($upcoming as $booking)
                <div class="flex flex-wrap items-center justify-between gap-2 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
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

    @include('main.sections')
@endsection
