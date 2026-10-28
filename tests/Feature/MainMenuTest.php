<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Menu utama (landing + beranda pelanggan). */
class MainMenuTest extends TestCase
{
    use RefreshDatabase;

    private function service(string $name, int $price = 50000, bool $active = true): Service
    {
        return Service::create([
            'name' => $name,
            'description' => 'Deskripsi '.$name,
            'price' => $price,
            'duration_minutes' => 45,
            'is_active' => $active,
        ]);
    }

    public function test_guest_sees_main_menu_sections(): void
    {
        $this->service('Cuci Aktif', 50000);

        $this->get('/')
            ->assertOk()
            ->assertSee('Selamat datang di')
            ->assertSee('Pesan Sekarang')
            ->assertSee('Menu Utama')
            ->assertSee('Layanan Pilihan')
            ->assertSee('Cara Pesan')
            ->assertSee('Cuci Aktif')
            ->assertSee('Rp 50.000')
            ->assertSee('Masuk')
            ->assertSee('Daftar');
    }

    public function test_service_card_links_to_booking_with_that_service(): void
    {
        $service = $this->service('Cuci Aktif');

        $this->get('/')->assertSee('/book?service='.$service->id, false);
    }

    public function test_guest_cannot_open_booking_and_is_sent_to_login(): void
    {
        $this->get('/book')->assertRedirect('/login');
    }

    public function test_customer_home_shows_greeting_and_main_menu(): void
    {
        $this->service('Cuci Aktif');
        $user = User::factory()->create(['role' => UserRole::Customer, 'name' => 'Budi Santoso']);

        $this->actingAs($user)->get('/home')
            ->assertOk()
            ->assertSee('Halo, Budi')
            ->assertSee('Booking berjalan')
            ->assertSee('Layanan Pilihan')
            ->assertSee('Cuci Aktif')
            ->assertSee('Keluar');
    }

    public function test_staff_sees_admin_panel_shortcut_instead_of_booking_menu(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Staff]);

        $this->actingAs($staff)->get('/')
            ->assertOk()
            ->assertSee('Buka Panel Admin')
            ->assertDontSee('Kendaraan Saya');
    }

    public function test_search_filters_services_by_keyword(): void
    {
        $this->service('Cuci Motor');
        $this->service('Detailing Interior');

        $this->get('/services?q=Motor')
            ->assertOk()
            ->assertSee('Cuci Motor')
            ->assertDontSee('Detailing Interior');
    }

    public function test_search_treats_percent_sign_literally(): void
    {
        $this->service('Cuci Motor');

        $this->get('/services?q=%25')->assertOk()->assertDontSee('Cuci Motor');
    }

    public function test_inactive_services_are_hidden_from_main_menu(): void
    {
        $this->service('Cuci Aktif');
        $this->service('Layanan Nonaktif', 10000, false);

        $this->get('/')->assertSee('Cuci Aktif')->assertDontSee('Layanan Nonaktif');
    }
}
