<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleBookingPageTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        return User::factory()->create(['role' => UserRole::Customer]);
    }

    private function vehicleFor(User $user, string $plate = 'B 1111 AAA'): Vehicle
    {
        return Vehicle::create([
            'user_id' => $user->id,
            'plate_number' => $plate,
            'type' => 'car',
            'brand' => 'Toyota',
            'color' => 'Hitam',
            'year' => 2020,
        ]);
    }

    private function service(): Service
    {
        return Service::create([
            'name' => 'Cuci Reguler',
            'price' => 50000,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);
    }

    public function test_customer_can_add_vehicle_and_plate_is_normalized(): void
    {
        $user = $this->customer();

        $this->actingAs($user)->post('/vehicles', [
            'plate_number' => '  b  2222   xyz ',
            'type' => 'car',
            'brand' => 'Honda',
            'color' => 'Hitam',
            'year' => 2020,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('vehicles', ['user_id' => $user->id, 'plate_number' => 'B 2222 XYZ']);
    }

    public function test_duplicate_plate_for_same_user_is_rejected(): void
    {
        $user = $this->customer();
        $this->vehicleFor($user, 'B 1111 AAA');

        $this->actingAs($user)->post('/vehicles', [
            'plate_number' => 'b 1111 aaa',
            'type' => 'car',
            'brand' => 'Honda',
            'color' => 'Hitam',
            'year' => 2020,
        ])->assertSessionHasErrors('plate_number');
    }

    public function test_customer_can_update_own_vehicle(): void
    {
        $user = $this->customer();
        $vehicle = $this->vehicleFor($user);

        $this->actingAs($user)->put("/vehicles/{$vehicle->id}", [
            'plate_number' => 'B 9999 ZZZ',
            'type' => 'suv',
            'brand' => 'Mitsubishi',
            'color' => 'Hitam',
            'year' => 2020,
            'model' => 'Pajero',
        ])->assertRedirect('/vehicles');

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'plate_number' => 'B 9999 ZZZ', 'type' => 'suv']);
    }

    public function test_update_may_keep_the_same_plate(): void
    {
        $user = $this->customer();
        $vehicle = $this->vehicleFor($user, 'B 1111 AAA');

        $this->actingAs($user)->put("/vehicles/{$vehicle->id}", [
            'plate_number' => 'B 1111 AAA',
            'type' => 'car',
            'brand' => 'Toyota Baru',
            'color' => 'Hitam',
            'year' => 2020,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'brand' => 'Toyota Baru']);
    }

    public function test_color_and_year_are_required_and_year_must_be_valid(): void
    {
        $user = $this->customer();
        $base = ['plate_number' => 'B 3333 CCC', 'type' => 'car', 'brand' => 'Honda'];

        $this->actingAs($user)->post('/vehicles', $base)
            ->assertSessionHasErrors(['color', 'year']);

        $this->actingAs($user)->post('/vehicles', $base + ['color' => 'Putih', 'year' => 1950])
            ->assertSessionHasErrors('year');

        $this->assertDatabaseMissing('vehicles', ['plate_number' => 'B 3333 CCC']);
    }

    public function test_vehicle_saves_color_year_and_optional_note(): void
    {
        $user = $this->customer();

        $this->actingAs($user)->post('/vehicles', [
            'plate_number' => 'B 4444 DDD',
            'type' => 'suv',
            'brand' => 'Toyota',
            'model' => 'Fortuner',
            'color' => 'Putih',
            'year' => 2022,
            'note' => 'Ada baret di pintu kiri',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('vehicles', [
            'user_id' => $user->id,
            'color' => 'Putih',
            'year' => 2022,
            'note' => 'Ada baret di pintu kiri',
        ]);
    }

    public function test_customer_cannot_edit_or_update_someone_elses_vehicle(): void
    {
        $owner = $this->customer();
        $other = $this->customer();
        $vehicle = $this->vehicleFor($owner);

        $this->actingAs($other)->get("/vehicles/{$vehicle->id}/edit")->assertForbidden();
        $this->actingAs($other)->put("/vehicles/{$vehicle->id}", [
            'plate_number' => 'B 5555 HAK',
            'type' => 'car',
            'brand' => 'X',
        ])->assertForbidden();
    }

    public function test_vehicle_with_booking_cannot_be_deleted(): void
    {
        $user = $this->customer();
        $vehicle = $this->vehicleFor($user);
        app(BookingService::class)->create($user, [
            'service_id' => $this->service()->id,
            'vehicle_id' => $vehicle->id,
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
        ]);

        $this->actingAs($user)->delete("/vehicles/{$vehicle->id}")->assertSessionHasErrors('vehicle');
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id]);
    }

    public function test_availability_shows_remaining_capacity_and_full_slots(): void
    {
        config(['bara.slot_capacity' => 2]);

        $user = $this->customer();
        $vehicle = $this->vehicleFor($user);
        $service = $this->service();
        $date = now()->addDay()->toDateString();
        $bookings = app(BookingService::class);

        $payload = ['service_id' => $service->id, 'vehicle_id' => $vehicle->id, 'booking_date' => $date, 'start_time' => '10:00'];
        $bookings->create($user, $payload);

        $this->assertSame(1, $bookings->availability($date)['10:00']['remaining']);
        $this->assertTrue($bookings->availability($date)['10:00']['available']);

        $bookings->create($user, $payload);

        $this->assertSame(0, $bookings->availability($date)['10:00']['remaining']);
        $this->assertFalse($bookings->availability($date)['10:00']['available']);
        $this->assertSame(2, $bookings->availability($date)['11:00']['remaining']);
    }

    public function test_cancelled_booking_frees_the_slot(): void
    {
        config(['bara.slot_capacity' => 1]);

        $user = $this->customer();
        $vehicle = $this->vehicleFor($user);
        $date = now()->addDay()->toDateString();
        $bookings = app(BookingService::class);

        $booking = $bookings->create($user, [
            'service_id' => $this->service()->id,
            'vehicle_id' => $vehicle->id,
            'booking_date' => $date,
            'start_time' => '09:00',
        ]);
        $this->assertFalse($bookings->availability($date)['09:00']['available']);

        $bookings->cancel($booking, $user, 'tes');
        $this->assertTrue($bookings->availability($date)['09:00']['available']);
    }

    public function test_booking_page_lists_slots_with_remaining_text(): void
    {
        $user = $this->customer();
        $this->vehicleFor($user);
        $this->service();
        $date = now()->addDay()->toDateString();

        $this->actingAs($user)->get("/book?date={$date}")
            ->assertOk()
            ->assertSee('08:00 —', false)
            ->assertSee('sisa 3');
    }

    public function test_booking_page_without_vehicle_asks_to_add_one(): void
    {
        $this->actingAs($this->customer())->get('/book')
            ->assertOk()
            ->assertSee('Tambah kendaraan dulu');
    }

    public function test_customer_can_book_and_sees_it_in_my_bookings(): void
    {
        $user = $this->customer();
        $vehicle = $this->vehicleFor($user);
        $service = $this->service();

        $this->actingAs($user)->post('/book', [
            'service_id' => $service->id,
            'vehicle_id' => $vehicle->id,
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => '13:00',
        ])->assertRedirect('/my-bookings');

        $this->actingAs($user)->get('/my-bookings')
            ->assertOk()
            ->assertSee('Cuci Reguler')
            ->assertSee('Menunggu Konfirmasi');
    }

    public function test_customer_cannot_cancel_someone_elses_booking(): void
    {
        $owner = $this->customer();
        $other = $this->customer();
        $booking = app(BookingService::class)->create($owner, [
            'service_id' => $this->service()->id,
            'vehicle_id' => $this->vehicleFor($owner)->id,
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
        ]);

        $this->actingAs($other)->post("/my-bookings/{$booking->id}/cancel")->assertForbidden();
    }
}
