<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

/** FASE 4: login Google (Socialite). Google asli tidak dipanggil: Socialite di-mock. */
class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    private function enableGoogle(): void
    {
        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);
    }

    private function fakeGoogleUser(string $id, string $email, string $name = 'Budi Google'): void
    {
        $googleUser = (new SocialiteUser)->map(['id' => $id, 'name' => $name, 'email' => $email]);

        Socialite::shouldReceive('driver->user')->andReturn($googleUser);
    }

    public function test_google_routes_return_404_when_not_configured(): void
    {
        config(['services.google.client_id' => null]);

        $this->get('/auth/google')->assertNotFound();
        $this->get('/auth/google/callback')->assertNotFound();
    }

    public function test_google_button_is_hidden_without_config_and_shown_with_config(): void
    {
        config(['services.google.client_id' => null]);
        $this->get('/login')->assertOk()->assertDontSee('Masuk dengan Google');

        $this->enableGoogle();
        $this->get('/login')->assertOk()->assertSee('Masuk dengan Google');
        $this->get('/register')->assertOk()->assertSee('Daftar dengan Google');
    }

    public function test_redirect_goes_to_google(): void
    {
        $this->enableGoogle();

        $this->get('/auth/google')->assertRedirectContains('accounts.google.com');
    }

    public function test_new_google_user_becomes_customer_and_goes_home(): void
    {
        $this->enableGoogle();
        $this->fakeGoogleUser('g-123', 'budi@gmail.com');

        $this->get('/auth/google/callback')->assertRedirect('/home');

        $user = User::where('email', 'budi@gmail.com')->firstOrFail();
        $this->assertSame('g-123', $user->google_id);
        $this->assertSame(UserRole::Customer, $user->role);
        $this->assertNull($user->password);
        $this->assertAuthenticatedAs($user);
    }

    public function test_existing_email_is_linked_and_keeps_role(): void
    {
        $this->enableGoogle();
        $staff = User::factory()->create(['email' => 'staff@gmail.com', 'role' => UserRole::Staff]);
        $this->fakeGoogleUser('g-777', 'staff@gmail.com');

        $this->get('/auth/google/callback')->assertRedirect('/admin');

        $this->assertSame('g-777', $staff->fresh()->google_id);
        $this->assertSame(UserRole::Staff, $staff->fresh()->role);
        $this->assertSame(1, User::where('email', 'staff@gmail.com')->count());
    }

    public function test_google_failure_returns_to_login_with_error(): void
    {
        $this->enableGoogle();
        Socialite::shouldReceive('driver->user')->andThrow(new \RuntimeException('invalid state'));

        $this->get('/auth/google/callback')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
