<?php

namespace Tests\Feature;

use App\Models\Setting;
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

    public function test_admin_creates_customer_with_app_name_and_sees_app_link(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.customers.create'))
            ->assertOk()
            ->assertSee('name="country_code"', false)
            ->assertSee('value="91"', false)
            ->assertSee('+91')
            ->assertDontSee('+92 Pakistan');

        $this->post(route('admin.customers.store'), [
                'name' => 'Sai Kiran',
                'country_code' => '91',
                'phone' => '9876543210',
                'status' => 'active',
                'app_name' => 'EasyCash',
                'available_credit' => 34500,
                'credit_min' => 2000,
                'credit_max' => 34500,
                'eligible_offer' => 50000,
            ])
            ->assertRedirect();

        $customer = User::query()->where('phone', '919876543210')->first();
        $this->assertNotNull($customer);
        $this->assertSame('EasyCash', $customer->app_name);

        $this->assertGreaterThan(6, strlen((string) $customer->app_token));

        $this->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee('EasyCash')
            ->assertSee('Send this app link')
            ->assertSee(\App\Support\AppBrand::brandedLocalUrl($customer))
            ->assertDontSee('Network (phone on same Wi‑Fi)')
            ->assertDontSee('?app=')
            ->assertDontSee('Create login link');
    }

    public function test_admin_sets_the_support_email(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.support-email.edit'))
            ->assertOk()
            ->assertSee('Support email');

        $this->put(route('admin.support-email.update'), [
            'support_email' => 'help@easycash.example',
        ])->assertRedirect(route('admin.support-email.edit'));

        $this->assertSame('help@easycash.example', Setting::getValue('support_email'));

        $this->get(route('admin.support-email.edit'))
            ->assertOk()
            ->assertSee('help@easycash.example');
    }
}
