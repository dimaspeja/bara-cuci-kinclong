<?php

namespace App\Http\Controllers;

use App\Models\Service;

// FASE 2: landing dan daftar layanan memakai data tabel services.
// Fase 6 menambah ringkasan booking di beranda pelanggan.
class HomeController extends Controller
{
    public function landing()
    {
        return view('landing', ['services' => $this->activeServices()]);
    }

    public function services()
    {
        return view('services', ['services' => $this->activeServices()]);
    }

    public function home()
    {
        return view('home');
    }

    private function activeServices()
    {
        return Service::where('is_active', true)->orderBy('price')->get();
    }
}
