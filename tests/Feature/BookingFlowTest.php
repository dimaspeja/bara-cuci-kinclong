<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Enums\WashJobStatus;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\BookingService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private function service(): Service
    {
        return Service::create([
            'name' => 'Cuci Reguler',
            'price' => 50000,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);
    }

    /** @return array{0: User, 1: Vehicle} */
    private function customerWithVehicle(): array
    {
        $user = User::factory()->create(['role' => UserRole::Customer]);
        $vehicle = Vehicle::create([
            'user_id' => $user->id,
            'plate_number' => 'B 1234 TES',
            'type' => 'car',
            'brand' => 'Toyota',
        ]);

        return [$user, $vehicle];
    }

    private function payload(Service $service, Vehicle $vehicle, string $time = '09:00'): array
    {
        return [
            'service_id' => $service->id,
            'vehicle_id' => $vehicle->id,
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => $time,
        ];
    }

    public function test_full_status_flow_creates_wash_job_and_logs(): void
    {
        [$customer, $vehicle] = $this->customerWithVehicle();
        $staff = User::factory()->create(['role' => UserRole::Staff]);
        $service = $this->service();
        $bookings = app(BookingService::class);

        $booking = $bookings->create($customer, $this->payload($service, $vehicle));
        $this->assertSame(BookingStatus::Pending, $booking->status);

        $bookings->confirm($booking, $staff);
        $this->assertSame(BookingStatus::Confirmed, $booking->fresh()->status);
        $this->assertSame(WashJobStatus::Waiting, $booking->washJob->status);

        $bookings->start($booking, $staff);
        $this->assertSame(BookingStatus::InProgress, $booking->fresh()->status);
        $this->assertSame($staff->id, $booking->fresh()->washJob->staff_id);

        $bookings->finish($booking, $staff);
        $this->assertSame(BookingStatus::Completed, $booking->fresh()->status);
        $this->assertSame(WashJobStatus::Done, $booking->fresh()->washJob->status);
        $this->assertNotNull($booking->fresh()->washJob->finished_at);

        // dibuat + dikonfirmasi + dimulai + selesai
        $this->assertSame(4, $booking->statusLogs()->count());
    }

    public function test_price_is_copied_to_booking(): void
    {
        [$customer, $vehicle] = $this->customerWithVehicle();
        $service = $this->service();

        $booking = app(BookingService::class)->create($customer, $this->payload($service, $vehicle));
        $service->update(['price' => 99000]);

        $this->assertSame('50000.00', $booking->fresh()->service_price);
    }

    public function test_slot_capacity_prevents_double_booking(): void
    {
        config(['bara.slot_capacity' => 1]);

        [$customer, $vehicle] = $this->customerWithVehicle();
        $service = $this->service();
        $bookings = app(BookingService::class);

        $bookings->create($customer, $this->payload($service, $vehicle));

        $this->expectException(ValidationException::class);
        $bookings->create($customer, $this->payload($service, $vehicle));
    }

    public function test_customer_cannot_cancel_after_confirmation(): void
    {
        [$customer, $vehicle] = $this->customerWithVehicle();
        $staff = User::factory()->create(['role' => UserRole::Staff]);
        $bookings = app(BookingService::class);

        $booking = $bookings->create($customer, $this->payload($this->service(), $vehicle));
        $bookings->confirm($booking, $staff);

        $this->expectException(DomainException::class);
        $bookings->cancel($booking, $customer, 'berubah pikiran');
    }

    public function test_completed_booking_cannot_move_back(): void
    {
        [$customer, $vehicle] = $this->customerWithVehicle();
        $staff = User::factory()->create(['role' => UserRole::Staff]);
        $bookings = app(BookingService::class);

        $booking = $bookings->create($customer, $this->payload($this->service(), $vehicle));
        $bookings->confirm($booking, $staff);
        $bookings->start($booking, $staff);
        $bookings->finish($booking, $staff);

        $this->expectException(DomainException::class);
        $bookings->cancel($booking, $staff, 'salah klik');
    }

    public function test_customer_gets_403_on_admin_panel(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->actingAs($customer)->get('/admin')->assertForbidden();
    }

    public function test_login_redirects_by_role(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect('/admin');
        $this->post('/logout');

        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect('/home');
    }

    public function test_wrong_password_shows_generic_message(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->post('/login', ['email' => $customer->email, 'password' => 'salahsalah'])
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);
    }
}
