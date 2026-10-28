<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Service;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /** Menu utama (publik). */
    public function landing()
    {
        return view('landing', [
            'services' => $this->activeServices(),
        ]);
    }

    /** Daftar layanan; kolom pencarian di navbar mengirim ?q=... */
    public function services(Request $request)
    {
        $keyword = trim((string) $request->query('q', ''));

        return view('services', [
            'services' => $this->activeServices($keyword),
        ]);
    }

    /** Beranda pelanggan: menu utama + ringkasan akun. */
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
            'services' => $this->activeServices(),
            'upcoming' => $upcoming,
            'vehicleCount' => $user->vehicles()->count(),
            'completedCount' => $user->bookings()->where('status', BookingStatus::Completed->value)->count(),
        ]);
    }

    private function activeServices(string $keyword = '')
    {
        return Service::where('is_active', true)
            ->when($keyword !== '', function ($query) use ($keyword) {
                $like = '%'.addcslashes($keyword, '%_\\').'%';
                $query->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('description', 'like', $like));
            })
            ->orderBy('price')
            ->get();
    }
}
