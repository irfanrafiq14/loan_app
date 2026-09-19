<?php

namespace Tests\Feature;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_open_another_customers_loan_payment_page(): void
    {
        $owner = User::factory()->customer()->create();
        $intruder = User::factory()->customer()->create();
        $loan = Loan::factory()->create([
            'user_id' => $owner->id,
            'status' => LoanStatus::Approved,
        ]);

        $this->actingAs($intruder)
            ->get(route('loans.pay', $loan))
            ->assertForbidden();
    }

    public function test_featured_loans_are_visible_to_every_customer(): void
    {
        $owner = User::factory()->customer()->create();
        $viewer = User::factory()->customer()->create();
        Loan::factory()->create([
            'user_id' => $owner->id,
            'title' => 'Sweet Money',
            'status' => LoanStatus::Approved,
            'is_featured' => true,
        ]);

        $this->actingAs($viewer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Sweet Money')
            ->assertSee('Apply')
            ->assertDontSee('Approved');
    }

    public function test_apply_is_blocked_when_customer_has_outstanding_dues(): void
    {
        $customer = User::factory()->customer()->create();
        $due = Loan::factory()->create([
            'user_id' => $customer->id,
            'title' => 'Quick Boost',
            'status' => LoanStatus::Approved,
        ]);
        $offer = Loan::factory()->create([
            'title' => 'Growth Plus',
            'status' => LoanStatus::Approved,
            'is_featured' => true,
        ]);

        $this->actingAs($customer)
            ->postJson(route('loans.apply.store', $offer))
            ->assertStatus(422)
            ->assertJson([
                'ok' => false,
                'message' => 'Clear your previous dues before applying.',
            ]);

        $this->actingAs($customer)
            ->post(route('loans.apply.store', $offer))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', 'Clear your previous dues before applying.');

        $this->assertDatabaseMissing('loans', [
            'user_id' => $customer->id,
            'title' => 'Growth Plus',
        ]);
        $this->assertDatabaseHas('loans', [
            'id' => $due->id,
            'title' => 'Quick Boost',
        ]);
    }

    public function test_customer_can_apply_when_dues_are_clear(): void
    {
        $customer = User::factory()->customer()->create();
        $offer = Loan::factory()->create([
            'title' => 'Growth Plus',
            'amount' => 18000,
            'status' => LoanStatus::Approved,
            'is_featured' => true,
        ]);

        $this->actingAs($customer)
            ->postJson(route('loans.apply.store', $offer))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('message', 'Processing...');

        $this->assertDatabaseHas('loans', [
            'user_id' => $customer->id,
            'title' => 'Growth Plus',
            'amount' => 18000,
            'status' => LoanStatus::Pending->value,
        ]);
    }

    public function test_apply_uses_selected_slider_amount_within_offer_range(): void
    {
        $customer = User::factory()->customer()->create();
        $offer = Loan::factory()->create([
            'title' => 'Festival Cash',
            'amount' => 200000,
            'minimum_amount' => 10000,
            'maximum_amount' => 200000,
            'status' => LoanStatus::Approved,
            'is_featured' => true,
            'user_id' => null,
        ]);

        $this->actingAs($customer)
            ->postJson(route('loans.apply.store', $offer), ['amount' => 50000])
            ->assertOk();

        $this->assertDatabaseHas('loans', [
            'user_id' => $customer->id,
            'title' => 'Festival Cash',
            'amount' => 50000,
            'status' => LoanStatus::Pending->value,
        ]);
    }

    public function test_home_progress_bar_uses_pending_then_active_loan_amount(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Pending loan amount')
            ->assertDontSee('Active loan')
            ->assertSee('Rs. 0')
            ->assertSee('Max Rs. 0');

        Loan::factory()->create([
            'user_id' => $customer->id,
            'amount' => 200,
            'minimum_amount' => 10000,
            'maximum_amount' => 200000,
            'status' => LoanStatus::Pending,
        ]);

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Pending loan amount')
            ->assertSee('Rs. 200')
            ->assertSee('Min Rs. 10,000')
            ->assertSee('Max Rs. 200,000');

        Loan::query()->where('user_id', $customer->id)->update([
            'status' => LoanStatus::Approved->value,
        ]);

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Pending loan amount')
            ->assertDontSee('Active loan')
            ->assertSee('Rs. 200')
            ->assertSee('Min Rs. 10,000')
            ->assertSee('Max Rs. 200,000');

        Loan::factory()->create([
            'user_id' => $customer->id,
            'amount' => 15000,
            'minimum_amount' => 2000,
            'maximum_amount' => 28000,
            'status' => LoanStatus::Pending,
        ]);

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Pending loan amount')
            ->assertSee('Rs. 15,200')
            ->assertSee('Min Rs. 2,000')
            ->assertSee('Max Rs. 200,000');
    }

    public function test_admin_created_loan_appears_on_customer_orders_page(): void
    {
        $customer = User::factory()->customer()->create();
        Loan::factory()->create([
            'user_id' => $customer->id,
            'title' => 'Sweet Money',
            'status' => LoanStatus::Pending,
        ]);

        $this->actingAs($customer)
            ->get(route('orders', ['tab' => 'pending']))
            ->assertOk()
            ->assertSee('Sweet Money')
            ->assertSee('Pending')
            ->assertDontSee('Active loans');
    }
}
