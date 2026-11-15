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
        @php
            $input = 'mt-1 w-full rounded-md border border-slate-300 px-3 py-2';
            $chosenTime = old('start_time');
            if (! $chosenTime || ! ($availability[$chosenTime]['available'] ?? false)) {
                $chosenTime = collect($availability)->filter(fn ($s) => $s['available'])->keys()->first();
            }
            $openSlots = collect($availability)->filter(fn ($s) => $s['available'])->count();
        @endphp

        <form id="booking-form" method="POST" action="{{ route('bookings.store') }}"
              class="mt-6 max-w-lg space-y-4 rounded-xl border border-slate-200 bg-white p-5">
            @csrf

            <div>
                <label for="service_id" class="text-sm font-medium">Layanan</label>
                <select id="service_id" name="service_id" required class="{{ $input }}">
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
                <select id="vehicle_id" name="vehicle_id" required class="{{ $input }}">
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}"
                            @selected((int) old('vehicle_id', $selectedVehicle) === $vehicle->id)>
                            {{ $vehicle->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="booking_date" class="text-sm font-medium">Tanggal</label>
                    <input id="booking_date" name="booking_date" type="date" required
                           min="{{ now()->toDateString() }}" value="{{ old('booking_date', $date) }}"
                           class="{{ $input }}">
                </div>
                <div>
                    <label for="start_time" class="text-sm font-medium">Jam</label>
                    <select id="start_time" name="start_time" required class="{{ $input }}">
                        @foreach ($availability as $slot => $info)
                            <option value="{{ $slot }}" @disabled(! $info['available']) @selected($chosenTime === $slot)>
                                {{ $slot }} —
                                @if ($info['past']) lewat
                                @elseif ($info['remaining'] === 0) penuh
                                @else sisa {{ $info['remaining'] }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if ($openSlots === 0)
                <p class="rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-800">
                    Semua jam pada tanggal ini sudah penuh atau lewat. Pilih tanggal lain.
                </p>
            @endif

            <div>
                <label for="notes" class="text-sm font-medium">Catatan (opsional)</label>
                <input id="notes" name="notes" value="{{ old('notes') }}" maxlength="255" class="{{ $input }}">
            </div>

            <button @disabled($openSlots === 0)
                    class="w-full rounded-md bg-sky-600 py-2 font-medium text-white hover:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-60">
                Buat booking
            </button>
        </form>

        {{-- Ganti tanggal: muat ulang halaman agar sisa kuota jam ikut berubah. --}}
        <script>
            document.getElementById('booking_date').addEventListener('change', function () {
                if (!this.value) return;
                var q = new URLSearchParams({ date: this.value });
                var service = document.getElementById('service_id').value;
                var vehicle = document.getElementById('vehicle_id').value;
                if (service) q.set('service', service);
                if (vehicle) q.set('vehicle', vehicle);
                window.location = '{{ route('bookings.create') }}?' + q.toString();
            });
        </script>
    @endif
@endsection
