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

    public function test_home_progress_bar_uses_customer_account_limits_and_loan_amount(): void
    {
        $customer = User::factory()->customer()->create([
            'credit_min' => 0,
            'credit_max' => 34500,
        ]);

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Selected amount')
            ->assertDontSee('Active loan')
            ->assertSee('₹0')
            ->assertSee('₹34,500');

        Loan::factory()->create([
            'user_id' => $customer->id,
            'amount' => 1200,
            'total_due' => 1200,
            'minimum_amount' => 10000,
            'maximum_amount' => 200000,
            'status' => LoanStatus::Pending,
        ]);

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Selected amount')
            ->assertSee('₹1,200')
            ->assertSee('₹0')
            ->assertSee('₹34,500')
            ->assertDontSee('₹10,000')
            ->assertDontSee('₹200,000');

        Loan::query()->where('user_id', $customer->id)->update([
            'status' => LoanStatus::Approved->value,
        ]);

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Selected amount')
            ->assertDontSee('Active loan')
            ->assertSee('₹1,200')
            ->assertSee('₹34,500');

        Loan::factory()->create([
            'user_id' => $customer->id,
            'amount' => 4050,
            'total_due' => 4050,
            'minimum_amount' => 2000,
            'maximum_amount' => 28000,
            'status' => LoanStatus::Pending,
        ]);

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Selected amount')
            ->assertSee('₹5,250')
            ->assertSee('₹0')
            ->assertSee('₹34,500')
            ->assertDontSee('₹28,000');
    }

    public function test_admin_created_loan_shows_card_fields_on_customer_orders_page(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $this->actingAs($admin)
            ->get(route('admin.loans.create'))
            ->assertOk()
            ->assertSee('Total due')
            ->assertSee('Loan amount')
            ->assertSee('Loan date')
            ->assertSee('Due date')
            ->assertDontSee('Minimum amount')
            ->assertDontSee('Payment instructions')
            ->assertDontSee('name="description"', false)
            ->assertDontSee('name="status"', false);

        $this->post(route('admin.loans.store'), [
            'user_id' => $customer->id,
            'title' => 'Sweet Money',
            'total_due' => 5250,
            'amount' => 2750,
            'loan_date' => '2026-08-22',
            'due_date' => '2026-09-03',
        ])->assertRedirect();

        $loan = Loan::query()->where('user_id', $customer->id)->where('title', 'Sweet Money')->first();
        $this->assertNotNull($loan);
        $this->assertNotEmpty($loan->reference_code);
        $this->assertEquals(5250, (float) $loan->total_due);
        $this->assertEquals(2750, (float) $loan->amount);

        $this->actingAs($customer)
            ->get(route('orders', ['tab' => 'pending']))
            ->assertOk()
            ->assertSee('Active obligations')
            ->assertSee('Sweet Money')
            ->assertSee('Reference: '.$loan->reference())
            ->assertSee('Total due')
            ->assertSee('₹5,250')
            ->assertSee('Due Sep 3, 2026')
            ->assertSee('View Details')
            ->assertSee('Pay Now')
            ->assertSee('Loan amount')
            ->assertSee('₹2,750')
            ->assertSee('Aug 22, 2026')
            ->assertDontSee('Active loans');

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Selected amount')
            ->assertSee('₹5,250')
            ->assertSee('₹34,500');
    }
}
