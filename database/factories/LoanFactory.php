<?php

namespace Database\Factories;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Loan>
 */
class LoanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->customer(),
            'title' => fake()->randomElement(['Sweet Money', 'Quick Boost', 'Growth Plus']),
            'amount' => 25250,
            'minimum_amount' => 2000,
            'maximum_amount' => 34500,
            'loan_date' => now()->toDateString(),
            'due_date' => now()->addMonths(1)->toDateString(),
            'description' => 'Demo loan created for the customer app.',
            'payment_instructions' => 'Transfer the amount using a configured method and upload your receipt.',
            'status' => LoanStatus::Approved,
            'is_featured' => false,
        ];
    }
}
