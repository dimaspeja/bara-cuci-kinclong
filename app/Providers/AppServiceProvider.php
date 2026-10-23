<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Semua waktu disimpan dan ditampilkan dalam WIB.
        $timezone = config('bara.timezone', 'Asia/Jakarta');
        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);

        Carbon::setLocale('id');
    }
}
