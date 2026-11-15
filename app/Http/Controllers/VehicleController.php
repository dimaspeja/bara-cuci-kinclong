<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        return view('vehicles.index', [
            'vehicles' => $request->user()->vehicles()->orderBy('plate_number')->get(),
            'types' => Vehicle::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Rapikan plat: huruf besar, spasi tunggal.
        $request->merge([
            'plate_number' => strtoupper(preg_replace('/\s+/', ' ', trim((string) $request->input('plate_number')))),
        ]);

        $data = $request->validate([
            'plate_number' => [
                'required', 'string', 'max:15',
                Rule::unique('vehicles', 'plate_number')->where('user_id', $user->id)->where('is_deleted', 0),
            ],
            'type' => ['required', Rule::in(array_keys(Vehicle::TYPES))],
            'brand' => ['required', 'string', 'max:50'],
            'model' => ['nullable', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:30'],
            'year' => ['required', 'integer', 'between:1980,'.(now()->year + 1)],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $user->vehicles()->create($data);

        return back()->with('status', 'Kendaraan disimpan.');
    }

    public function edit(Request $request, Vehicle $vehicle)
    {
        abort_unless($vehicle->user_id === $request->user()->id, 403);

        return view('vehicles.edit', ['vehicle' => $vehicle, 'types' => Vehicle::TYPES]);
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        abort_unless($vehicle->user_id === $request->user()->id, 403);

        $request->merge([
            'plate_number' => strtoupper(preg_replace('/\s+/', ' ', trim((string) $request->input('plate_number')))),
        ]);

        $data = $request->validate([
            'plate_number' => [
                'required', 'string', 'max:15',
                Rule::unique('vehicles', 'plate_number')
                    ->where('user_id', $request->user()->id)
                    ->where('is_deleted', 0)
                    ->ignore($vehicle->id),
            ],
            'type' => ['required', Rule::in(array_keys(Vehicle::TYPES))],
            'brand' => ['required', 'string', 'max:50'],
            'model' => ['nullable', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:30'],
            'year' => ['required', 'integer', 'between:1980,'.(now()->year + 1)],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $vehicle->update($data);

        return redirect()->route('vehicles.index')->with('status', 'Data kendaraan diperbarui.');
    }

    public function destroy(Request $request, Vehicle $vehicle): RedirectResponse
    {
        abort_unless($vehicle->user_id === $request->user()->id, 403);

        if ($vehicle->bookings()->exists()) {
            return back()->withErrors(['vehicle' => 'Kendaraan ini sudah punya riwayat booking dan tidak bisa dihapus.']);
        }

        $vehicle->delete();

        return back()->with('status', 'Kendaraan dihapus.');
    }
}
