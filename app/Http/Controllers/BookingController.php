<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Services\BookingService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        return view('bookings.index', [
            'bookings' => $request->user()->bookings()
                ->with(['service', 'vehicle'])
                ->orderByDesc('booking_date')
                ->orderByDesc('start_time')
                ->get(),
        ]);
    }

    public function create(Request $request, BookingService $bookings)
    {
        return view('bookings.create', [
            'services' => Service::where('is_active', true)->orderBy('price')->get(),
            'vehicles' => $request->user()->vehicles()->orderBy('plate_number')->get(),
            'slots' => $bookings->slots(),
            'selectedService' => $request->integer('service'),
        ]);
    }

    public function store(Request $request, BookingService $bookings): RedirectResponse
    {
        $data = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        // Pelanggaran slot / kapasitas dilempar sebagai ValidationException oleh service.
        $booking = $bookings->create($request->user(), $data);

        return redirect()->route('bookings.index')
            ->with('status', "Booking {$booking->code} dibuat. Menunggu konfirmasi staff.");
    }

    public function cancel(Request $request, Booking $booking, BookingService $bookings): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        try {
            $bookings->cancel($booking, $request->user(), 'Dibatalkan oleh pelanggan');
        } catch (DomainException $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }

        return back()->with('status', "Booking {$booking->code} dibatalkan.");
    }
}
