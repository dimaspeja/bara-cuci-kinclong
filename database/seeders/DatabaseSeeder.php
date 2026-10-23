<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

// FASE 1: hanya tiga akun demo. Fase berikutnya menambah layanan, kendaraan, dan booking contoh.
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['Admin Bara', 'admin@barakinclong.test', UserRole::Admin, null],
            ['Staff Carwash', 'staff@barakinclong.test', UserRole::Staff, null],
            ['Pelanggan Demo', 'pelanggan@barakinclong.test', UserRole::Customer, '081234567890'],
        ];

        foreach ($accounts as [$name, $email, $role, $phone]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'phone' => $phone,
                'password' => 'password123', // di-hash otomatis oleh cast "hashed"
                'role' => $role,
                'email_verified_at' => now(),
            ]);
        }
    }
}
