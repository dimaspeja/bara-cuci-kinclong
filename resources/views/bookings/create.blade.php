@extends('layouts.app')

@section('title', 'Booking · Bara Cuci Kinclong')

@section('content')
    <h1 class="text-2xl font-bold">Booking baru</h1>
    <p class="text-sm text-slate-500">Harga hanya informasi; pembayaran dilakukan di lokasi.</p>

    @if ($vehicles->isEmpty())
        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            Anda belum punya kendaraan tersimpan.
            <a href="{{ route('vehicles.index') }}" class="font-medium underline">Tambah kendaraan dulu</a>.
        </div>
    @else
        <form method="POST" action="{{ route('bookings.store') }}"
              class="mt-6 max-w-lg space-y-4 rounded-xl border border-slate-200 bg-white p-5">
            @csrf

            <div>
                <label for="service_id" class="text-sm font-medium">Layanan</label>
                <select id="service_id" name="service_id" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                    <option value="">— Pilih layanan —</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}"
                            @selected((int) old('service_id', $selectedService) === $service->id)>
                            {{ $service->name }} — {{ $service->priceLabel() }} · {{ $service->duration_minutes }} mnt
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="vehicle_id" class="text-sm font-medium">Kendaraan</label>
                <select id="vehicle_id" name="vehicle_id" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected((int) old('vehicle_id') === $vehicle->id)>
                            {{ $vehicle->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="booking_date" class="text-sm font-medium">Tanggal</label>
                    <input id="booking_date" name="booking_date" type="date" required
                           min="{{ now()->toDateString() }}" value="{{ old('booking_date', now()->toDateString()) }}"
                           class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label for="start_time" class="text-sm font-medium">Jam</label>
                    <select id="start_time" name="start_time" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                        @foreach ($slots as $slot)
                            <option value="{{ $slot }}" @selected(old('start_time') === $slot)>{{ $slot }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="notes" class="text-sm font-medium">Catatan (opsional)</label>
                <input id="notes" name="notes" value="{{ old('notes') }}" maxlength="255"
                       class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
            </div>

            <button class="w-full rounded-md bg-sky-600 py-2 font-medium text-white hover:bg-sky-700">Buat booking</button>
        </form>
    @endif
@endsection
