<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DefaultFieldsTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        return User::factory()->create(['role' => UserRole::Customer]);
    }

    private function payload(string $plate = 'B 7777 GGG'): array
    {
        return [
            'plate_number' => $plate,
            'type' => 'car',
            'brand' => 'Honda',
            'model' => 'Brio',
            'color' => 'Biru',
            'year' => 2020,
            'note' => 'Ada baret di bemper belakang',
        ];
    }

    public function test_every_main_table_has_the_seven_default_fields(): void
    {
        $workflow = ['bookings', 'wash_jobs'];

        foreach (['users', 'services', 'vehicles', 'bookings', 'wash_jobs', 'booking_status_logs'] as $table) {
            $statusColumn = in_array($table, $workflow, true) ? 'record_status' : 'status';

            $this->assertTrue(
                Schema::hasColumns($table, ['id', $statusColumn, 'is_deleted', 'created_by', 'created_at', 'updated_by', 'updated_at']),
                "Tabel {$table} belum punya 7 default field."
            );
        }
    }

    public function test_created_by_and_updated_by_are_filled_from_the_logged_in_user(): void
    {
        $user = $this->customer();

        $this->actingAs($user)->post('/vehicles', $this->payload())->assertSessionHasNoErrors();

        $vehicle = Vehicle::firstOrFail();
        $this->assertEquals($user->id, $vehicle->created_by);
        $this->assertEquals($user->id, $vehicle->updated_by);
        $this->assertEquals(1, $vehicle->status);
        $this->assertEquals(0, $vehicle->is_deleted);
    }

    public function test_updated_by_changes_when_another_user_edits(): void
    {
        $first = $this->customer();
        $second = $this->customer();

        $this->actingAs($first);
        $vehicle = Vehicle::create($this->payload() + ['user_id' => $first->id]);

        $this->actingAs($second);
        $vehicle->update(['brand' => 'Toyota']);

        $fresh = $vehicle->fresh();
        $this->assertEquals($first->id, $fresh->created_by);
        $this->assertEquals($second->id, $fresh->updated_by);
    }

    public function test_delete_only_marks_is_deleted_and_hides_the_row(): void
    {
        $user = $this->customer();
        $this->actingAs($user)->post('/vehicles', $this->payload());
        $vehicle = Vehicle::firstOrFail();

        $this->actingAs($user)->delete("/vehicles/{$vehicle->id}")->assertSessionHasNoErrors();

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'is_deleted' => 1]);
        $this->assertNull(Vehicle::find($vehicle->id));
        $this->assertNotNull(Vehicle::withoutGlobalScopes()->find($vehicle->id));
        $this->actingAs($user)->get('/vehicles')->assertDontSee('B 7777 GGG');
    }

    public function test_plate_can_be_added_again_after_the_vehicle_was_deleted(): void
    {
        $user = $this->customer();
        $this->actingAs($user)->post('/vehicles', $this->payload());
        $vehicle = Vehicle::firstOrFail();
        $this->actingAs($user)->delete("/vehicles/{$vehicle->id}");

        $this->actingAs($user)->post('/vehicles', $this->payload())->assertSessionHasNoErrors();

        $this->assertEquals(1, Vehicle::where('plate_number', 'B 7777 GGG')->count());
    }
}
