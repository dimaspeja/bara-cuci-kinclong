@extends('layouts.app')

@section('title', 'Kendaraan · Bara Cuci Kinclong')

@section('content')
    <h1 class="text-2xl font-bold">Kendaraan saya</h1>

    <div class="mt-6 grid gap-6 md:grid-cols-5">
        <div class="md:col-span-3">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-2">Plat</th>
                            <th class="px-4 py-2">Jenis</th>
                            <th class="px-4 py-2">Merek / Model</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($vehicles as $vehicle)
                            <tr>
                                <td class="px-4 py-2 font-medium">{{ $vehicle->plate_number }}</td>
                                <td class="px-4 py-2">{{ $vehicle->typeLabel() }}</td>
                                <td class="px-4 py-2">{{ $vehicle->brand }} {{ $vehicle->model }}</td>
                                <td class="px-4 py-2 text-right">
                                    <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}"
                                          onsubmit="return confirm('Hapus kendaraan ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-rose-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">Belum ada kendaraan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="md:col-span-2">
            <form method="POST" action="{{ route('vehicles.store') }}" class="space-y-3 rounded-xl border border-slate-200 bg-white p-4">
                @csrf
                <h2 class="font-semibold">Tambah kendaraan</h2>
                <div>
                    <label class="text-sm font-medium" for="plate_number">Plat nomor</label>
                    <input id="plate_number" name="plate_number" value="{{ old('plate_number') }}" required placeholder="B 1234 ABC"
                           class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 uppercase">
                </div>
                <div>
                    <label class="text-sm font-medium" for="type">Jenis</label>
                    <select id="type" name="type" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium" for="brand">Merek</label>
                    <input id="brand" name="brand" value="{{ old('brand') }}" required placeholder="Toyota"
                           class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="text-sm font-medium" for="model">Model (opsional)</label>
                    <input id="model" name="model" value="{{ old('model') }}" placeholder="Avanza"
                           class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                </div>
                <button class="w-full rounded-md bg-sky-600 py-2 font-medium text-white hover:bg-sky-700">Simpan</button>
            </form>
        </div>
    </div>
@endsection
