<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Service;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function landing()
    {
        return view('landing', [
            'services' => Service::where('is_active', true)->orderBy('price')->get(),
        ]);
    }

    public function services()
    {
        return view('services', [
            'services' => Service::where('is_active', true)->orderBy('price')->get(),
        ]);
    }

    /** Beranda pelanggan. */
    public function home(Request $request)
    {
        $user = $request->user();

        $upcoming = $user->bookings()
            ->with(['service', 'vehicle'])
            ->whereIn('status', [
                BookingStatus::Pending->value,
                BookingStatus::Confirmed->value,
                BookingStatus::InProgress->value,
            ])
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        return view('home', [
            'upcoming' => $upcoming,
            'vehicleCount' => $user->vehicles()->count(),
            'completedCount' => $user->bookings()->where('status', BookingStatus::Completed->value)->count(),
        ]);
    }
}
