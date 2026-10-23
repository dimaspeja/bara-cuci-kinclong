<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function user(UserRole $role): User
    {
        // Kata sandi bawaan factory: "password"
        return User::factory()->create(['role' => $role]);
    }

    public function test_customer_login_goes_to_home(): void
    {
        $user = $this->user(UserRole::Customer);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_and_staff_login_go_to_admin_panel(): void
    {
        foreach ([UserRole::Admin, UserRole::Staff] as $role) {
            $user = $this->user($role);

            $this->post('/login', ['email' => $user->email, 'password' => 'password'])
                ->assertRedirect('/admin');

            $this->post('/logout');
        }
    }

    public function test_wrong_password_shows_generic_message(): void
    {
        $user = $this->user(UserRole::Customer);

        $this->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'salahsalah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);
        $this->assertGuest();
    }

    public function test_empty_fields_show_validation_errors(): void
    {
        $this->post('/login', [])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_login_is_throttled_after_five_failures(): void
    {
        $user = $this->user(UserRole::Customer);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'salahsalah']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/home')->assertRedirect('/login');
    }

    public function test_customer_gets_403_on_admin_panel(): void
    {
        $this->actingAs($this->user(UserRole::Customer))->get('/admin')->assertForbidden();
    }

    public function test_staff_gets_403_on_customer_home(): void
    {
        $this->actingAs($this->user(UserRole::Staff))->get('/home')->assertForbidden();
    }

    public function test_register_creates_customer_and_logs_in(): void
    {
        $this->post('/register', [
            'name' => 'Budi Baru',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect('/home');

        $user = User::where('email', 'budi@example.com')->firstOrFail();
        $this->assertSame(UserRole::Customer, $user->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        $user = $this->user(UserRole::Customer);

        $this->post('/register', [
            'name' => 'Dobel',
            'email' => $user->email,
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasErrors('email');
    }
}
