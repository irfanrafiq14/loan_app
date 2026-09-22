<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\AppBrand;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_login_with_phone_and_otp(): void
    {
        $customer = User::factory()->customer()->create([
            'name' => 'Muhammad Irfan',
            'phone' => '929959591151',
            'app_name' => 'EasyCash',
        ]);

        $this->post(route('client.login.phone'), [
            'country_code' => '92',
            'phone' => PhoneNumber::localPart($customer->phone),
        ])->assertRedirect(route('verify-otp.show'));

        $this->get(route('verify-otp.show'))
            ->assertOk()
            ->assertSee('EasyCash')
            ->assertSee('Welcome back, Muhammad Irfan');

        $this->post(route('verify-otp.verify'), [
            'otp' => '1234',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($customer);
    }

    public function test_customer_can_logout(): void
    {
        $customer = User::factory()->customer()->create([
            'phone' => '929959591151',
            'app_name' => 'testapp',
        ]);

        $this->post(route('client.login.phone'), [
            'country_code' => '92',
            'phone' => PhoneNumber::localPart($customer->phone),
        ]);
        $this->post(route('verify-otp.verify'), ['otp' => '1234']);

        $this->post(route('logout'))
            ->assertRedirect(route('client.login'));

        $this->assertGuest();

        $this->get(route('client.login'))
            ->assertOk()
            ->assertSee('Phone number')
            ->assertSee('Continue')
            ->assertSee('storedAppBrand', false)
            ->assertDontSee('MaxWallet')
            ->assertDontSee('Admin login');

        $this->post(route('client.login.phone'), [
            'country_code' => '92',
            'phone' => PhoneNumber::localPart($customer->phone),
        ])->assertRedirect(route('verify-otp.show'));

        $this->post(route('verify-otp.verify'), [
            'otp' => '1234',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($customer);
    }

    public function test_created_app_name_is_shown_throughout_the_customer_app(): void
    {
        $customer = User::factory()->customer()->create([
            'name' => 'Muhammad Irfan',
            'phone' => '929959591151',
            'app_name' => 'EasyCash',
        ]);

        $this->post(route('client.login.phone'), [
            'country_code' => '92',
            'phone' => PhoneNumber::localPart($customer->phone),
        ]);

        $this->get(route('verify-otp.show'))
            ->assertOk()
            ->assertSee('EasyCash');

        $this->post(route('verify-otp.verify'), [
            'otp' => '1234',
        ])->assertRedirect(route('home'));

        $this->assertSame('EasyCash', $customer->fresh()->app_name);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('EasyCash')
            ->assertSee('Based on your EasyCash profile')
            ->assertSee(AppBrand::faviconHref('EasyCash'), false)
            ->assertDontSee('MaxWallet');

        $this->get(route('orders'))
            ->assertOk()
            ->assertSee('EasyCash')
            ->assertDontSee('MaxWallet');

        $this->get(route('profile'))
            ->assertOk()
            ->assertSee('EasyCash')
            ->assertDontSee('MaxWallet');

        $this->post(route('logout'))
            ->assertRedirect(route('client.login'));

        $this->get(route('client.login'))
            ->assertOk()
            ->assertSee('Phone number')
            ->assertSee('Continue')
            ->assertSee('storedAppBrand', false)
            ->assertDontSee('MaxWallet')
            ->assertDontSee('Welcome, Muhammad Irfan');
    }

    public function test_logout_clears_saved_account_so_another_customer_can_sign_in(): void
    {
        $first = User::factory()->customer()->create([
            'name' => 'Muhammad Irfan',
            'phone' => '929959591151',
            'app_name' => 'testapp',
        ]);
        $second = User::factory()->customer()->create([
            'name' => 'Ayesha Khan',
            'phone' => '923001234567',
            'app_name' => 'EasyCash',
        ]);

        $this->actingAs($first);
        AppBrand::rememberClient($first, 'testapp');

        $this->post(route('logout'))
            ->assertRedirect(route('client.login'));

        $this->assertGuest();

        $this->get(route('client.login'))
            ->assertOk()
            ->assertSee('Phone number')
            ->assertSee('storedAppBrand', false)
            ->assertDontSee('Welcome, Muhammad Irfan')
            ->assertDontSee('MaxWallet');

        $this->post(route('client.login.phone'), [
            'country_code' => '92',
            'phone' => '3001234567',
        ])->assertRedirect(route('verify-otp.show'));

        $this->get(route('verify-otp.show'))
            ->assertOk()
            ->assertSee('EasyCash')
            ->assertSee('Welcome back, Ayesha Khan');

        $this->post(route('verify-otp.verify'), [
            'otp' => '1234',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($second);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('EasyCash')
            ->assertDontSee('testapp');
    }

    public function test_existing_customer_can_sign_in_from_another_device_with_phone(): void
    {
        $customer = User::factory()->customer()->create([
            'name' => 'Muhammad Irfan',
            'phone' => '929959591151',
            'app_name' => 'EasyCash',
        ]);

        $this->get(route('client.login'))
            ->assertOk()
            ->assertSee('Welcome')
            ->assertSee('Phone number')
            ->assertDontSee('Welcome, Muhammad Irfan')
            ->assertDontSee('EasyCash')
            ->assertDontSee('MaxWallet');

        $this->post(route('client.login.phone'), [
            'country_code' => '92',
            'phone' => '9959591151',
        ])->assertRedirect(route('verify-otp.show'));

        $this->get(route('verify-otp.show'))
            ->assertOk()
            ->assertSee('EasyCash')
            ->assertSee('Welcome back, Muhammad Irfan');

        $this->post(route('verify-otp.verify'), [
            'otp' => '1234',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($customer);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('EasyCash')
            ->assertDontSee('MaxWallet');
    }

    public function test_unknown_phone_number_cannot_open_the_app(): void
    {
        $this->post(route('client.login.phone'), [
            'country_code' => '92',
            'phone' => '3001234567',
        ])->assertSessionHasErrors('phone');
    }

    public function test_favicon_uses_first_letter_of_app_name(): void
    {
        $this->assertSame('T', AppBrand::initial('testapp'));
        $this->assertSame('E', AppBrand::initial('EasyCash'));

        $customer = User::factory()->customer()->create([
            'app_name' => 'testapp',
        ]);

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee(AppBrand::faviconHref('testapp'), false);
    }

    public function test_invalid_country_code_is_rejected(): void
    {
        $this->post(route('client.login.phone'), [
            'country_code' => '999',
            'phone' => '9959591151',
        ])->assertSessionHasErrors('country_code');
    }

    public function test_old_access_links_redirect_to_normal_login(): void
    {
        $this->get('/access/old-token')
            ->assertRedirect(route('client.login', ['t' => 'oldtoken']));
    }

    public function test_first_visit_token_shows_app_name_and_short_tokens_are_ignored(): void
    {
        $customer = User::factory()->customer()->create([
            'name' => 'Muhammad Irfan',
            'phone' => '929959591151',
            'app_name' => 'EasyCash',
        ]);

        $this->assertGreaterThan(6, strlen((string) $customer->app_token));

        $this->get('/')
            ->assertOk()
            ->assertSee('Welcome')
            ->assertSee('Phone number')
            ->assertSee('Continue')
            ->assertDontSee('EasyCash')
            ->assertDontSee('MaxWallet')
            ->assertDontSee('Admin login');

        $this->get('/?t=abc123')
            ->assertOk()
            ->assertSee('Welcome')
            ->assertDontSee('EasyCash');

        $this->get('/?app=EasyCash')
            ->assertOk()
            ->assertDontSee('EasyCash')
            ->assertDontSee('MaxWallet');

        $this->get('/?t='.$customer->app_token)
            ->assertOk()
            ->assertSee('EasyCash')
            ->assertDontSee('MaxWallet');

        $this->post(route('client.login.phone'), [
            'country_code' => '92',
            'phone' => PhoneNumber::localPart($customer->phone),
        ]);

        $this->get(route('verify-otp.show'))
            ->assertOk()
            ->assertSee('EasyCash');

        $this->post(route('verify-otp.verify'), ['otp' => '1234']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('EasyCash')
            ->assertDontSee('MaxWallet');
    }

    public function test_support_button_opens_gmail_with_admin_email(): void
    {
        $customer = User::factory()->customer()->create([
            'name' => 'Muhammad Irfan',
            'phone' => '929959591151',
            'app_name' => 'EasyCash',
        ]);

        Setting::putValue('support_email', 'help@easycash.example');

        $this->get('/')
            ->assertOk()
            ->assertSee('Support')
            ->assertSee('https://mail.google.com/mail/', false)
            ->assertSee('to=help%40easycash.example', false);

        $this->post(route('client.login.phone'), [
            'country_code' => '92',
            'phone' => PhoneNumber::localPart($customer->phone),
        ]);
        $this->post(route('verify-otp.verify'), ['otp' => '1234']);

        $this->get(route('profile'))
            ->assertOk()
            ->assertSee('https://mail.google.com/mail/', false)
            ->assertSee('to=help%40easycash.example', false);
    }
}
