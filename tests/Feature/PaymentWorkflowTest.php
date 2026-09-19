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
                'transaction_id' => 'TXN-1001',
                'screenshot' => $this->fakeScreenshot(),
            ])
            ->assertRedirect(route('orders', ['tab' => 'pending']));

        $this->assertDatabaseHas('loan_payments', [
            'loan_id' => $loan->id,
            'user_id' => $customer->id,
            'transaction_id' => 'TXN-1001',
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
                'transaction_id' => 'TXN-1001',
                'screenshot' => UploadedFile::fake()->create('notes.pdf', 120, 'application/pdf'),
            ])
            ->assertSessionHasErrors('screenshot');

        $this->actingAs($customer)
            ->post(route('payments.store'), [
                'loan_id' => $loan->id,
                'payment_method_id' => $method->id,
                'transaction_id' => 'TXN-1001',
                'screenshot' => UploadedFile::fake()->create('huge.jpg', 6000, 'image/jpeg'),
            ])
            ->assertSessionHasErrors('screenshot');
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
                'transaction_id' => 'TXN-2002',
                'screenshot' => $this->fakeScreenshot(),
            ])
            ->assertForbidden();
    }
}
