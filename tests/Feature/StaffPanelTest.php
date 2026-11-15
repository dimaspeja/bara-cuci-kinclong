<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Enums\WashJobStatus;
use App\Filament\Resources\BookingResource\Pages\ListBookings;
use App\Filament\Resources\WashJobResource\Pages\ListWashJobs;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\BookingService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StaffPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function staff(): User
    {
        return User::factory()->create(['role' => UserRole::Staff]);
    }

    private function pendingBooking(): Booking
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
        $service = Service::create([
            'name' => 'Cuci Reguler',
            'price' => 50000,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);

        return app(BookingService::class)->create($customer, [
            'service_id' => $service->id,
            'vehicle_id' => $vehicle->id,
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
        ]);
    }

    public function test_staff_can_open_booking_and_queue_pages(): void
    {
        $this->actingAs($this->staff());

        $this->get('/admin/bookings')->assertOk();
        $this->get('/admin/wash-jobs')->assertOk();
    }

    public function test_customer_cannot_open_booking_and_queue_pages(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->actingAs($customer)->get('/admin/bookings')->assertForbidden();
        $this->actingAs($customer)->get('/admin/wash-jobs')->assertForbidden();
    }

    public function test_staff_can_confirm_a_booking_from_the_table(): void
    {
        $staff = $this->staff();
        $booking = $this->pendingBooking();

        $this->actingAs($staff);
        Livewire::test(ListBookings::class)
            ->callTableAction('confirm', $booking)
            ->assertHasNoTableActionErrors();

        $booking->refresh();
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
        $this->assertSame(WashJobStatus::Waiting, $booking->washJob->status);
        $this->assertEquals($staff->id, $booking->updated_by);
    }

    public function test_staff_can_cancel_a_booking_with_a_reason(): void
    {
        $staff = $this->staff();
        $booking = $this->pendingBooking();

        $this->actingAs($staff);
        Livewire::test(ListBookings::class)
            ->callTableAction('cancel', $booking, data: ['reason' => 'Pelanggan berhalangan'])
            ->assertHasNoTableActionErrors();

        $booking->refresh();
        $this->assertSame(BookingStatus::Cancelled, $booking->status);
        $this->assertSame('Pelanggan berhalangan', $booking->cancel_reason);
    }

    public function test_cancel_requires_a_reason(): void
    {
        $booking = $this->pendingBooking();

        $this->actingAs($this->staff());
        Livewire::test(ListBookings::class)
            ->callTableAction('cancel', $booking, data: ['reason' => ''])
            ->assertHasTableActionErrors(['reason' => 'required']);

        $this->assertSame(BookingStatus::Pending, $booking->fresh()->status);
    }

    public function test_staff_can_start_and_finish_from_the_wash_queue(): void
    {
        $staff = $this->staff();
        $booking = $this->pendingBooking();
        app(BookingService::class)->confirm($booking, $staff);
        $job = $booking->fresh()->washJob;

        $this->actingAs($staff);
        $queue = Livewire::test(ListWashJobs::class);

        $queue->assertTableActionVisible('start', $job)
            ->assertTableActionHidden('finish', $job)
            ->callTableAction('start', $job);

        $booking->refresh();
        $this->assertSame(BookingStatus::InProgress, $booking->status);
        $this->assertEquals($staff->id, $booking->washJob->staff_id);

        $queue->assertTableActionVisible('finish', $job->fresh())
            ->callTableAction('finish', $job->fresh());

        $booking->refresh();
        $this->assertSame(BookingStatus::Completed, $booking->status);
        $this->assertSame(WashJobStatus::Done, $booking->washJob->status);
    }
}
