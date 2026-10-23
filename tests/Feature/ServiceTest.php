<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** FASE 2: menu Layanan (publik + panel admin). */
class ServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(string $name, bool $active = true): Service
    {
        return Service::create([
            'name' => $name,
            'description' => 'Deskripsi '.$name,
            'price' => 50000,
            'duration_minutes' => 45,
            'is_active' => $active,
        ]);
    }

    private function user(UserRole $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_public_page_lists_only_active_services(): void
    {
        $this->service('Cuci Aktif', true);
        $this->service('Layanan Nonaktif', false);

        $this->get('/services')
            ->assertOk()
            ->assertSee('Cuci Aktif')
            ->assertDontSee('Layanan Nonaktif');
    }

    public function test_price_is_shown_in_rupiah_format(): void
    {
        $this->service('Cuci Aktif');

        $this->get('/services')->assertSee('Rp 50.000');
        $this->assertSame('Rp 50.000', Service::first()->priceLabel());
    }

    public function test_landing_page_shows_active_services(): void
    {
        $this->service('Cuci Aktif', true);
        $this->service('Layanan Nonaktif', false);

        $this->get('/')
            ->assertOk()
            ->assertSee('Cuci Aktif')
            ->assertDontSee('Layanan Nonaktif');
    }

    public function test_admin_can_open_service_pages(): void
    {
        $service = $this->service('Cuci Aktif');
        $admin = $this->user(UserRole::Admin);

        $this->actingAs($admin)->get('/admin/services')->assertOk();
        $this->actingAs($admin)->get('/admin/services/create')->assertOk();
        $this->actingAs($admin)->get("/admin/services/{$service->id}/edit")->assertOk();
    }

    public function test_staff_cannot_open_service_pages(): void
    {
        $service = $this->service('Cuci Aktif');
        $staff = $this->user(UserRole::Staff);

        $this->actingAs($staff)->get('/admin/services')->assertForbidden();
        $this->actingAs($staff)->get('/admin/services/create')->assertForbidden();
        $this->actingAs($staff)->get("/admin/services/{$service->id}/edit")->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_from_service_pages(): void
    {
        $this->get('/admin/services')->assertRedirect('/login');
    }
}
