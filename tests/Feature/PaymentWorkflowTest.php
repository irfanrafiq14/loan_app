<?php

namespace Tests\Feature;

use App\Enums\LoanStatus;
use App\Enums\PaymentStatus;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_submit_a_payment(): void
    {
        Storage::fake('local');

        $customer = User::factory()->customer()->create();
        $loan = Loan::factory()->create([
            'user_id' => $customer->id,
            'status' => LoanStatus::Approved,
        ]);
        $method = PaymentMethod::factory()->create();

        $this->actingAs($customer)
            ->post(route('payments.store'), [
                'loan_id' => $loan->id,
                'payment_method_id' => $method->id,
                'transaction_id' => 'TXN-10012567',
                'screenshot' => $this->fakeScreenshot(),
            ])
            ->assertRedirect(route('orders', ['tab' => 'pending']));

        $this->assertDatabaseHas('loan_payments', [
            'loan_id' => $loan->id,
            'user_id' => $customer->id,
            'transaction_id' => 'TXN-10012567',
            'status' => PaymentStatus::Pending->value,
        ]);
    }

    public function test_admin_can_approve_a_payment(): void
    {
        Storage::fake('local');

        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $loan = Loan::factory()->create([
            'user_id' => $customer->id,
            'status' => LoanStatus::Approved,
        ]);
        $payment = LoanPayment::factory()->forLoan($loan)->create();

        $this->actingAs($admin)
            ->post(route('admin.payments.approve', $payment), [
                'admin_notes' => 'Receipt verified.',
            ])
            ->assertRedirect(route('admin.payments.show', $payment));

        $this->assertSame(PaymentStatus::Completed, $payment->fresh()->status);
        $this->assertSame(LoanStatus::Completed, $loan->fresh()->status);

        $this->actingAs($customer)
            ->get(route('orders', ['tab' => 'completed']))
            ->assertSee($loan->title);
    }

    public function test_admin_can_reject_a_payment(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $loan = Loan::factory()->create([
            'user_id' => $customer->id,
            'status' => LoanStatus::Approved,
        ]);
        $payment = LoanPayment::factory()->forLoan($loan)->create();

        $this->actingAs($admin)
            ->post(route('admin.payments.reject', $payment), [
                'admin_notes' => 'Screenshot is unreadable.',
            ])
            ->assertRedirect(route('admin.payments.show', $payment));

        $this->assertSame(PaymentStatus::Rejected, $payment->fresh()->status);
        $this->assertSame(LoanStatus::Approved, $loan->fresh()->status);

        $this->actingAs($customer)
            ->get(route('orders', ['tab' => 'pending']))
            ->assertSee('Screenshot is unreadable.');
    }

    public function test_payment_screenshot_must_be_a_valid_image(): void
    {
        Storage::fake('local');

        $customer = User::factory()->customer()->create();
        $loan = Loan::factory()->create([
            'user_id' => $customer->id,
            'status' => LoanStatus::Approved,
        ]);
        $method = PaymentMethod::factory()->create();

        $this->actingAs($customer)
            ->post(route('payments.store'), [
                'loan_id' => $loan->id,
                'payment_method_id' => $method->id,
                'transaction_id' => 'TXN-10012567',
                'screenshot' => UploadedFile::fake()->create('notes.pdf', 120, 'application/pdf'),
            ])
            ->assertSessionHasErrors('screenshot');

        $this->actingAs($customer)
            ->post(route('payments.store'), [
                'loan_id' => $loan->id,
                'payment_method_id' => $method->id,
                'transaction_id' => 'TXN-10012567',
                'screenshot' => UploadedFile::fake()->create('huge.jpg', 6000, 'image/jpeg'),
            ])
            ->assertSessionHasErrors('screenshot');
    }

    public function test_transaction_id_must_be_at_least_12_characters(): void
    {
        Storage::fake('local');

        $customer = User::factory()->customer()->create();
        $loan = Loan::factory()->create([
            'user_id' => $customer->id,
            'status' => LoanStatus::Approved,
        ]);
        $method = PaymentMethod::factory()->create();

        $this->actingAs($customer)
            ->post(route('payments.store'), [
                'loan_id' => $loan->id,
                'payment_method_id' => $method->id,
                'transaction_id' => 'TXN-1001',
                'screenshot' => $this->fakeScreenshot(),
            ])
            ->assertSessionHasErrors('transaction_id');
    }

    public function test_customer_cannot_change_payment_status(): void
    {
        $customer = User::factory()->customer()->create();
        $loan = Loan::factory()->create(['user_id' => $customer->id]);
        $payment = LoanPayment::factory()->forLoan($loan)->create();

        $this->actingAs($customer)
            ->post(route('admin.payments.approve', $payment))
            ->assertForbidden();
    }

    public function test_duplicate_pending_payment_is_blocked(): void
    {
        Storage::fake('local');

        $customer = User::factory()->customer()->create();
        $loan = Loan::factory()->create([
            'user_id' => $customer->id,
            'status' => LoanStatus::Approved,
        ]);
        $method = PaymentMethod::factory()->create();
        LoanPayment::factory()->forLoan($loan)->create();

        $this->actingAs($customer)
            ->post(route('payments.store'), [
                'loan_id' => $loan->id,
                'payment_method_id' => $method->id,
                'transaction_id' => 'TXN-20022567',
                'screenshot' => $this->fakeScreenshot(),
            ])
            ->assertForbidden();
    }

    public function test_customer_sees_copyable_payment_link_and_cannot_edit_it(): void
    {
        $customer = User::factory()->customer()->create([
            'payment_link' => '7780286550@sbi',
        ]);
        $loan = Loan::factory()->create([
            'user_id' => $customer->id,
            'status' => LoanStatus::Approved,
        ]);

        $this->actingAs($customer)
            ->get(route('loans.pay', $loan))
            ->assertOk()
            ->assertSee('Make Payment')
            ->assertSee('12 digits required')
            ->assertSee('minlength="12"', false)
            ->assertSee('Copy link')
            ->assertSee('7780286550@sbi')
            ->assertSee('images/payments/upi.png', false)
            ->assertSee('images/payments/gpay.png', false)
            ->assertSee('images/payments/phonepe.png', false)
            ->assertSee('images/payments/paytm.png', false)
            ->assertSee('Only an administrator can change this link.')
            ->assertDontSee('name="account_number"', false)
            ->assertDontSee('name="payment_link"', false)
            ->assertDontSee('Add method');
    }

    public function test_each_customer_has_their_own_payment_link(): void
    {
        $admin = User::factory()->admin()->create();
        $first = User::factory()->customer()->create([
            'name' => 'Sai Kiran',
            'app_name' => 'EasyCash',
            'payment_link' => 'first-customer@upi',
        ]);
        $second = User::factory()->customer()->create([
            'name' => 'Ayesha Khan',
            'app_name' => 'testapp',
            'payment_link' => 'second-customer@upi',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.customers.edit', $first))
            ->assertOk()
            ->assertSee('name="payment_link"', false)
            ->assertSee('first-customer@upi');

        $this->put(route('admin.customers.update', $first), [
            'name' => $first->name,
            'country_code' => '91',
            'phone' => \App\Support\PhoneNumber::localPart($first->phone),
            'status' => 'active',
            'app_name' => $first->app_name,
            'payment_link' => 'sai-updated@upi',
            'available_credit' => $first->available_credit,
            'credit_min' => $first->credit_min,
            'credit_max' => $first->credit_max,
            'eligible_offer' => $first->eligible_offer,
        ])->assertRedirect(route('admin.customers.show', $first));

        $this->assertSame('sai-updated@upi', $first->fresh()->payment_link);
        $this->assertSame('second-customer@upi', $second->fresh()->payment_link);

        $firstLoan = Loan::factory()->create([
            'user_id' => $first->id,
            'status' => LoanStatus::Approved,
        ]);
        $secondLoan = Loan::factory()->create([
            'user_id' => $second->id,
            'status' => LoanStatus::Approved,
        ]);

        $this->actingAs($first)
            ->get(route('loans.pay', $firstLoan))
            ->assertOk()
            ->assertSee('sai-updated@upi')
            ->assertDontSee('second-customer@upi');

        $this->actingAs($second)
            ->get(route('loans.pay', $secondLoan))
            ->assertOk()
            ->assertSee('second-customer@upi')
            ->assertDontSee('sai-updated@upi');
    }
}
