@extends('layouts.app')

@section('title', 'Ubah Kendaraan · Bara Cuci Kinclong')

@section('content')
    @php
        $input = 'mt-1 w-full rounded-md border border-slate-300 px-3 py-2';
    @endphp

    <h1 class="text-2xl font-bold">Ubah kendaraan</h1>

    <form method="POST" action="{{ route('vehicles.update', $vehicle) }}"
          class="mt-6 max-w-lg space-y-3 rounded-xl border border-slate-200 bg-white p-5">
        @csrf @method('PUT')

        <div>
            <label class="text-sm font-medium" for="plate_number">Plat nomor</label>
            <input id="plate_number" name="plate_number" value="{{ old('plate_number', $vehicle->plate_number) }}" required
                   class="{{ $input }} uppercase">
        </div>
        <div>
            <label class="text-sm font-medium" for="type">Jenis</label>
            <select id="type" name="type" class="{{ $input }}">
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}" @selected(old('type', $vehicle->type) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-medium" for="brand">Merek</label>
            <input id="brand" name="brand" value="{{ old('brand', $vehicle->brand) }}" required class="{{ $input }}">
        </div>
        <div>
            <label class="text-sm font-medium" for="model">Model (opsional)</label>
            <input id="model" name="model" value="{{ old('model', $vehicle->model) }}" class="{{ $input }}">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-sm font-medium" for="color">Warna</label>
                <input id="color" name="color" value="{{ old('color', $vehicle->color) }}" required class="{{ $input }}">
            </div>
            <div>
                <label class="text-sm font-medium" for="year">Tahun</label>
                <input id="year" name="year" type="number" min="1980" max="{{ now()->year + 1 }}" value="{{ old('year', $vehicle->year) }}" required class="{{ $input }}">
            </div>
        </div>
        <div>
            <label class="text-sm font-medium" for="note">Catatan (opsional)</label>
            <input id="note" name="note" value="{{ old('note', $vehicle->note) }}" class="{{ $input }}">
        </div>

        <div class="flex gap-3 pt-1">
            <button class="flex-1 rounded-md bg-sky-600 py-2 font-medium text-white hover:bg-sky-700">Simpan perubahan</button>
            <a href="{{ route('vehicles.index') }}" class="flex-1 rounded-md border border-slate-300 py-2 text-center font-medium hover:bg-slate-50">Batal</a>
        </div>
    </form>
@endsection
