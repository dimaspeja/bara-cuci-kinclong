@extends('layouts.app')

@section('title', 'Kendaraan · Bara Cuci Kinclong')

@section('content')
    @php
        $input = 'mt-1 w-full rounded-md border border-slate-300 px-3 py-2';
    @endphp

    <h1 class="text-2xl font-bold">Kendaraan saya</h1>

    <div class="mt-6 grid gap-6 md:grid-cols-12">
        <div class="md:col-span-8">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-2">Plat</th>
                            <th class="px-4 py-2">Jenis</th>
                            <th class="px-4 py-2">Merek / Model</th>
                            <th class="px-4 py-2">Warna</th>
                            <th class="px-4 py-2">Tahun</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($vehicles as $vehicle)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-2 font-medium">{{ $vehicle->plate_number }}</td>
                                <td class="whitespace-nowrap px-4 py-2">{{ $vehicle->typeLabel() }}</td>
                                <td class="px-4 py-2">
                                    {{ $vehicle->brand }} {{ $vehicle->model }}
                                    @if ($vehicle->note)
                                        <div class="text-xs text-slate-500">{{ $vehicle->note }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-2">{{ $vehicle->color }}</td>
                                <td class="px-4 py-2">{{ $vehicle->year }}</td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center justify-end gap-3 whitespace-nowrap">
                                        <a href="{{ route('vehicles.edit', $vehicle) }}" class="text-sky-600 hover:underline">Ubah</a>
                                        <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}"
                                              onsubmit="return confirm('Hapus kendaraan ini?')">
                                            @csrf @method('DELETE')
                                            <button class="text-rose-600 hover:underline">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada kendaraan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="md:col-span-4">
            <form method="POST" action="{{ route('vehicles.store') }}" class="space-y-3 rounded-xl border border-slate-200 bg-white p-4">
                @csrf
                <h2 class="font-semibold">Tambah kendaraan</h2>
                <div>
                    <label class="text-sm font-medium" for="plate_number">Plat nomor</label>
                    <input id="plate_number" name="plate_number" value="{{ old('plate_number') }}" required placeholder="B 1234 ABC"
                           class="{{ $input }} uppercase">
                </div>
                <div>
                    <label class="text-sm font-medium" for="type">Jenis</label>
                    <select id="type" name="type" class="{{ $input }}">
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium" for="brand">Merek</label>
                    <input id="brand" name="brand" value="{{ old('brand') }}" required placeholder="Toyota" class="{{ $input }}">
                </div>
                <div>
                    <label class="text-sm font-medium" for="model">Model (opsional)</label>
                    <input id="model" name="model" value="{{ old('model') }}" placeholder="Avanza" class="{{ $input }}">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium" for="color">Warna</label>
                        <input id="color" name="color" value="{{ old('color') }}" required placeholder="Hitam" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="text-sm font-medium" for="year">Tahun</label>
                        <input id="year" name="year" type="number" min="1980" max="{{ now()->year + 1 }}" value="{{ old('year') }}" required placeholder="2020" class="{{ $input }}">
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium" for="note">Catatan (opsional)</label>
                    <input id="note" name="note" value="{{ old('note') }}" placeholder="Ada baret di pintu kiri" class="{{ $input }}">
                </div>
                <button class="w-full rounded-md bg-sky-600 py-2 font-medium text-white hover:bg-sky-700">Simpan</button>
            </form>
        </div>
    </div>
@endsection
