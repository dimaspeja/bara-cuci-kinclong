<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// FASE 4: Fase 2 + login Google.
// Rute kendaraan dan booking ditambahkan di fase berikutnya.

Route::get('/', [HomeController::class, 'landing'])->name('landing');
Route::get('/services', [HomeController::class, 'services'])->name('services');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Area pelanggan (Admin juga boleh). Staff yang membuka /home mendapat 403.
Route::middleware(['auth', 'role:customer,admin'])->group(function () {
    Route::get('/home', [HomeController::class, 'home'])->name('home');
});
