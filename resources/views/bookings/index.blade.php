@extends('layouts.app')

@section('title', 'Booking Saya · Bara Cuci Kinclong')

@section('content')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Booking saya</h1>
        <a href="{{ route('bookings.create') }}" class="rounded-md bg-sky-600 px-4 py-1.5 text-sm text-white hover:bg-sky-700">Booking baru</a>
    </div>

    <div class="mt-6 space-y-3">
        @forelse ($bookings as $booking)
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="font-semibold">{{ $booking->service->name }}
                            <span class="text-xs font-normal text-slate-400">· {{ $booking->code }}</span></p>
                        <p class="text-sm text-slate-500">
                            {{ $booking->booking_date->translatedFormat('l, d M Y') }}, {{ $booking->timeLabel() }}
                            · {{ $booking->vehicle->plate_number }}
                        </p>
                        <p class="text-sm text-slate-500">
                            {{ $booking->priceLabel() }} · ± {{ $booking->duration_minutes }} menit
                        </p>
                        @if ($booking->cancel_reason)
                            <p class="mt-1 text-xs text-rose-600">Alasan batal: {{ $booking->cancel_reason }}</p>
                        @endif
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <x-status-badge :status="$booking->status" />
                        @if ($booking->status === \App\Enums\BookingStatus::Pending)
                            <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                                  onsubmit="return confirm('Batalkan booking ini?')">
                                @csrf
                                <button class="text-sm text-rose-600 hover:underline">Batalkan</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-slate-500">Belum ada booking.</p>
        @endforelse
    </div>
@endsection
