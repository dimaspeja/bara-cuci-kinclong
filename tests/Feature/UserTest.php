<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/** FASE 3: menu Pengguna (Admin kelola staff dan peran). */
class UserTest extends TestCase
{
    use RefreshDatabase;

    private function user(UserRole $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_admin_can_open_user_pages(): void
    {
        $admin = $this->user(UserRole::Admin);
        $target = $this->user(UserRole::Staff);

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/users/create')->assertOk();
        $this->actingAs($admin)->get("/admin/users/{$target->id}/edit")->assertOk();
    }

    public function test_staff_and_customer_cannot_open_user_pages(): void
    {
        $target = $this->user(UserRole::Staff);

        foreach ([UserRole::Staff, UserRole::Customer] as $role) {
            $this->actingAs($this->user($role));

            $this->get('/admin/users')->assertForbidden();
            $this->get('/admin/users/create')->assertForbidden();
            $this->get("/admin/users/{$target->id}/edit")->assertForbidden();
        }
    }

    public function test_admin_sees_users_in_list(): void
    {
        $admin = $this->user(UserRole::Admin);
        $users = collect([$this->user(UserRole::Staff), $this->user(UserRole::Customer)]);

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)->assertCanSeeTableRecords($users);
    }

    public function test_admin_can_create_staff_with_hashed_password(): void
    {
        $this->actingAs($this->user(UserRole::Admin));

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Staff Baru',
                'email' => 'staffbaru@example.com',
                'role' => UserRole::Staff->value,
                'password' => 'rahasia123',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $created = User::where('email', 'staffbaru@example.com')->firstOrFail();

        $this->assertSame(UserRole::Staff, $created->role);
        $this->assertNotSame('rahasia123', $created->password);
        $this->assertTrue(Hash::check('rahasia123', $created->password));
    }

    public function test_create_rejects_duplicate_email_and_short_password(): void
    {
        $existing = $this->user(UserRole::Customer);
        $this->actingAs($this->user(UserRole::Admin));

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Dobel',
                'email' => $existing->email,
                'role' => UserRole::Staff->value,
                'password' => 'rahasia123',
            ])
            ->call('create')
            ->assertHasFormErrors(['email']);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Sandi Pendek',
                'email' => 'pendek@example.com',
                'role' => UserRole::Staff->value,
                'password' => 'abc',
            ])
            ->call('create')
            ->assertHasFormErrors(['password']);
    }

    public function test_edit_keeps_password_when_left_blank_and_can_change_role(): void
    {
        $this->actingAs($this->user(UserRole::Admin));
        $target = $this->user(UserRole::Customer);
        $oldHash = $target->password;

        Livewire::test(EditUser::class, ['record' => $target->getRouteKey()])
            ->fillForm([
                'name' => 'Nama Baru',
                'role' => UserRole::Staff->value,
                'password' => '',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $target->refresh();

        $this->assertSame('Nama Baru', $target->name);
        $this->assertSame(UserRole::Staff, $target->role);
        $this->assertSame($oldHash, $target->password);
    }

    public function test_new_staff_can_log_in_and_reach_panel(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Staff]);

        $this->post('/login', ['email' => $staff->email, 'password' => 'password'])
            ->assertRedirect('/admin');
        $this->get('/admin')->assertOk();
        $this->get('/admin/users')->assertForbidden();
    }
}
