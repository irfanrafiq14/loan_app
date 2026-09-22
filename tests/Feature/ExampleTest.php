<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_shows_client_login_not_admin(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Welcome')
            ->assertSee('Phone number')
            ->assertSee('name="country_code"', false)
            ->assertSee('value="91"', false)
            ->assertSee('+91')
            ->assertDontSee('+92 Pakistan')
            ->assertSee('Continue')
            ->assertDontSee('Admin login')
            ->assertDontSee('MaxWallet')
            ->assertDontSee('Open the private login link');
    }

    public function test_admin_path_shows_admin_login(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSee('MaxWallet Admin')
            ->assertSee('Sign in');
    }
}
