<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\ServiceResource\Pages\CreateService;
use App\Filament\Resources\ServiceResource\Pages\EditService;
use App\Filament\Resources\ServiceResource\Pages\ListServices;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\BookingService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** CRUD 3: Layanan (data master yang dikelola Admin di panel /admin). */
class ServiceCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($this->admin);
    }

    private function service(string $name = 'Cuci Reguler', bool $active = true, int $price = 50000): Service
    {
        return Service::create([
            'name' => $name,
            'description' => 'Deskripsi '.$name,
            'price' => $price,
            'duration_minutes' => 45,
            'is_active' => $active,
        ]);
    }

    private function bookingFor(Service $service): Booking
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'plate_number' => 'B 1234 TES',
            'type' => 'car',
            'brand' => 'Toyota',
            'color' => 'Hitam',
            'year' => 2020,
        ]);

        return app(BookingService::class)->create($customer, [
            'service_id' => $service->id,
            'vehicle_id' => $vehicle->id,
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
        ]);
    }

    // ---------- CREATE ----------

    public function test_admin_can_create_service_and_default_fields_are_filled(): void
    {
        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => 'Cuci Premium',
                'description' => 'Cuci luar dalam',
                'price' => 85000,
                'duration_minutes' => 90,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('services', [
            'name' => 'Cuci Premium',
            'duration_minutes' => 90,
            'is_active' => 1,
            'is_deleted' => 0,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);
    }

    public function test_create_requires_name_price_and_duration(): void
    {
        Livewire::test(CreateService::class)
            ->fillForm(['name' => '', 'price' => null, 'duration_minutes' => null])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'price' => 'required', 'duration_minutes' => 'required']);
    }

    public function test_create_rejects_negative_price_and_out_of_range_duration(): void
    {
        Livewire::test(CreateService::class)
            ->fillForm(['name' => 'Cuci Cepat', 'price' => -1, 'duration_minutes' => 1])
            ->call('create')
            ->assertHasFormErrors(['price', 'duration_minutes']);

        Livewire::test(CreateService::class)
            ->fillForm(['name' => 'Cuci Lama', 'price' => 1000, 'duration_minutes' => 5000])
            ->call('create')
            ->assertHasFormErrors(['duration_minutes']);
    }

    public function test_create_rejects_duplicate_name(): void
    {
        $this->service('Cuci Reguler');

        Livewire::test(CreateService::class)
            ->fillForm(['name' => 'Cuci Reguler', 'price' => 40000, 'duration_minutes' => 30])
            ->call('create')
            ->assertHasFormErrors(['name' => 'unique']);

        $this->assertSame(1, Service::where('name', 'Cuci Reguler')->count());
    }

    // ---------- READ ----------

    public function test_list_shows_services_and_can_search_by_name(): void
    {
        $reguler = $this->service('Cuci Reguler');
        $wax = $this->service('Cuci Wax');

        Livewire::test(ListServices::class)
            ->assertCanSeeTableRecords([$reguler, $wax])
            ->searchTable('Wax')
            ->assertCanSeeTableRecords([$wax])
            ->assertCanNotSeeTableRecords([$reguler]);
    }

    public function test_list_tabs_split_active_and_inactive(): void
    {
        $aktif = $this->service('Cuci Reguler', true);
        $nonaktif = $this->service('Cuci Lama', false);

        Livewire::test(ListServices::class)
            ->set('activeTab', 'aktif')
            ->assertCanSeeTableRecords([$aktif])
            ->assertCanNotSeeTableRecords([$nonaktif])
            ->set('activeTab', 'nonaktif')
            ->assertCanSeeTableRecords([$nonaktif])
            ->assertCanNotSeeTableRecords([$aktif]);
    }

    // ---------- UPDATE ----------

    public function test_admin_can_update_service_and_updated_by_is_recorded(): void
    {
        $service = $this->service();
        $other = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($other);

        Livewire::test(EditService::class, ['record' => $service->getKey()])
            ->fillForm(['name' => 'Cuci Reguler Plus', 'price' => 60000])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Cuci Reguler Plus',
            'updated_by' => $other->id,
        ]);
    }

    public function test_editing_own_name_does_not_trigger_unique_error(): void
    {
        $service = $this->service('Cuci Reguler');

        Livewire::test(EditService::class, ['record' => $service->getKey()])
            ->fillForm(['price' => 55000])
            ->call('save')
            ->assertHasNoFormErrors();
    }

    public function test_changing_price_does_not_change_existing_booking_price(): void
    {
        $service = $this->service('Cuci Reguler', true, 50000);
        $booking = $this->bookingFor($service);

        Livewire::test(EditService::class, ['record' => $service->getKey()])
            ->fillForm(['price' => 99000])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(50000, $booking->fresh()->service_price);
        $this->assertEquals(99000, $service->fresh()->price);
    }

    public function test_service_can_be_deactivated_and_disappears_from_public_page(): void
    {
        $service = $this->service('Cuci Reguler');

        Livewire::test(EditService::class, ['record' => $service->getKey()])
            ->fillForm(['is_active' => false])
            ->call('save');

        $this->get('/services')->assertOk()->assertDontSee('Cuci Reguler');
    }

    // ---------- DELETE ----------

    public function test_service_without_booking_is_soft_deleted(): void
    {
        $service = $this->service('Cuci Reguler');

        Livewire::test(ListServices::class)
            ->callTableAction('hapus', $service)
            ->assertNotified('Layanan dihapus.');

        // Baris tetap ada di database, hanya ditandai terhapus.
        $this->assertDatabaseHas('services', ['id' => $service->id, 'is_deleted' => 1]);
        $this->assertNull(Service::find($service->id));

        $this->get('/services')->assertDontSee('Cuci Reguler');
    }

    public function test_service_with_booking_cannot_be_deleted(): void
    {
        $service = $this->service('Cuci Reguler');
        $this->bookingFor($service);

        Livewire::test(ListServices::class)
            ->callTableAction('hapus', $service)
            ->assertNotified('Layanan tidak bisa dihapus');

        $this->assertDatabaseHas('services', ['id' => $service->id, 'is_deleted' => 0]);
    }

    public function test_deleted_service_name_can_be_used_again(): void
    {
        $service = $this->service('Cuci Reguler');
        $service->delete();

        Livewire::test(CreateService::class)
            ->fillForm(['name' => 'Cuci Reguler', 'price' => 50000, 'duration_minutes' => 45])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(1, Service::where('name', 'Cuci Reguler')->count());
    }

    // ---------- AKSES ----------

    public function test_staff_and_customer_cannot_manage_services(): void
    {
        $service = $this->service();

        foreach ([UserRole::Staff, UserRole::Customer] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));

            $this->get('/admin/services')->assertForbidden();
            $this->get('/admin/services/create')->assertForbidden();
            $this->get("/admin/services/{$service->id}/edit")->assertForbidden();
        }
    }
}
