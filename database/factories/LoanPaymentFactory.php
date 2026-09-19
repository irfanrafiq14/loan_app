<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoanPayment>
 */
class LoanPaymentFactory extends Factory
{
    public function definition(): array
    {
        $loan = Loan::factory()->create();

        return [
            'loan_id' => $loan->id,
            'user_id' => $loan->user_id,
            'payment_method_id' => PaymentMethod::factory(),
            'transaction_id' => 'TXN-'.fake()->numerify('######'),
            'screenshot_path' => 'payment-screenshots/demo.jpg',
            'status' => PaymentStatus::Pending,
            'admin_notes' => null,
            'submitted_at' => now(),
            'reviewed_at' => null,
        ];
    }

    public function forLoan(Loan $loan): static
    {
        return $this->state(fn () => [
            'loan_id' => $loan->id,
            'user_id' => $loan->user_id,
        ]);
    }
}
