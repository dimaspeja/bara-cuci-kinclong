<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_goes_to_home_even_if_admin_was_opened_before_login(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->withSession(['url.intended' => url('/admin')])
            ->post('/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect('/home');
    }

    public function test_customer_still_returns_to_non_admin_page_opened_before_login(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->withSession(['url.intended' => url('/services')])
            ->post('/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect(url('/services'));
    }

    public function test_customer_without_previous_page_goes_to_home(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect('/home');
    }
}
