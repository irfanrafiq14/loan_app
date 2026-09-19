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
            ->assertSee('Sign in')
            ->assertSee('Phone number')
            ->assertSee('name="country_code"', false)
            ->assertSee('+92 Pakistan')
            ->assertSee('+91 India')
            ->assertDontSee('Admin login')
            ->assertDontSee('MaxWallet Admin')
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
