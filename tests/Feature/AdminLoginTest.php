<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_and_view_dashboard(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@maxwallet.test',
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'admin@maxwallet.test',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Total customers');

        $this->get('/admin')->assertRedirect(route('admin.dashboard'));
    }

    public function test_customer_cannot_use_admin_login(): void
    {
        $customer = User::factory()->customer()->create([
            'email' => 'customer@maxwallet.test',
            'password' => 'password',
        ]);

        $this->post(route('admin.login.store'), [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
