<?php

namespace Database\Seeders;

use App\Enums\LoanStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->create([
            'name' => 'MaxWallet Admin',
            'email' => 'admin@maxwallet.test',
            'phone' => null,
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'status' => UserStatus::Active,
        ]);

        Setting::putValue('support_email', 'help@easycash.example');

        $irfan = User::query()->create([
            'name' => 'Muhammad Irfan',
            'email' => null,
            'phone' => '929959591151',
            'password' => null,
            'role' => UserRole::Customer,
            'status' => UserStatus::Active,
            'available_credit' => 34500,
            'credit_min' => 2000,
            'credit_max' => 34500,
            'eligible_offer' => 50000,
            'app_name' => 'testapp',
            'support_email' => 'support@testapp.example',
        ]);

        $ayesha = User::query()->create([
            'name' => 'Ayesha Khan',
            'email' => null,
            'phone' => '923001234567',
            'password' => null,
            'role' => UserRole::Customer,
            'status' => UserStatus::Active,
            'available_credit' => 28000,
            'credit_min' => 2000,
            'credit_max' => 28000,
            'eligible_offer' => 40000,
            'app_name' => 'EasyCash',
            'support_email' => 'help@easycash.example',
        ]);

        $methods = [
            ['Google Pay', '778028656@omni', 'Copy the payment link, pay in this app, then submit the transaction ID and screenshot.', 1],
            ['PhonePe', '778028656@omni', 'Copy the payment link, pay in this app, then submit the transaction ID and screenshot.', 2],
            ['Paytm', '778028656@omni', 'Copy the payment link, pay in this app, then submit the transaction ID and screenshot.', 3],
            ['UPI', '778028656@omni', 'Copy the payment link, pay in this app, then submit the transaction ID and screenshot.', 4],
        ];

        $createdMethods = collect($methods)->map(fn ($method) => PaymentMethod::query()->create([
            'name' => $method[0],
            'account_number' => $method[1],
            'instructions' => $method[2],
            'is_active' => true,
            'sort_order' => $method[3],
        ]));

        Loan::query()->create([
            'user_id' => null,
            'title' => 'Sweet Money',
            'amount' => 25250,
            'minimum_amount' => 2000,
            'maximum_amount' => 34500,
            'description' => 'Featured offer available to every customer.',
            'payment_instructions' => 'Transfer the loan amount using a configured method.',
            'status' => LoanStatus::Approved,
            'is_featured' => true,
        ]);

        Loan::query()->create([
            'user_id' => null,
            'title' => 'Growth Plus',
            'amount' => 18000,
            'minimum_amount' => 2000,
            'maximum_amount' => 34500,
            'description' => 'Featured growth offer.',
            'payment_instructions' => 'Transfer the loan amount using a configured method.',
            'status' => LoanStatus::Approved,
            'is_featured' => true,
        ]);

        $sweetMoney = Loan::query()->create([
            'user_id' => $irfan->id,
            'title' => 'Sweet Money',
            'amount' => 2750,
            'total_due' => 5250,
            'minimum_amount' => 2000,
            'maximum_amount' => 34500,
            'loan_date' => '2026-08-22',
            'due_date' => '2026-09-03',
            'description' => 'Featured offer for Muhammad Irfan.',
            'payment_instructions' => 'Transfer ₹25,250 to the account shown on the payment page.',
            'status' => LoanStatus::Approved,
        ]);

        Loan::query()->create([
            'user_id' => $irfan->id,
            'title' => 'Growth Plus',
            'amount' => 18000,
            'minimum_amount' => 2000,
            'maximum_amount' => 34500,
            'loan_date' => '2026-07-01',
            'due_date' => '2026-09-01',
            'description' => 'Completed demo loan.',
            'payment_instructions' => 'Already settled.',
            'status' => LoanStatus::Completed,
        ]);

        Loan::query()->create([
            'user_id' => $ayesha->id,
            'title' => 'Quick Boost',
            'amount' => 15000,
            'minimum_amount' => 2000,
            'maximum_amount' => 28000,
            'loan_date' => now()->toDateString(),
            'due_date' => now()->addMonth()->toDateString(),
            'description' => 'Active loan for Ayesha Khan.',
            'payment_instructions' => 'Use any active payment method.',
            'status' => LoanStatus::Pending,
        ]);

        $demoPath = 'payment-screenshots/'.$irfan->id.'/demo.jpg';
        Storage::disk('local')->put($demoPath, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));

        LoanPayment::query()->create([
            'loan_id' => $sweetMoney->id,
            'user_id' => $irfan->id,
            'payment_method_id' => $createdMethods->first()->id,
            'transaction_id' => 'TXN-10025100',
            'screenshot_path' => $demoPath,
            'status' => PaymentStatus::Pending,
            'submitted_at' => now()->subHour(),
        ]);

        $completedLoan = Loan::query()->where('title', 'Growth Plus')->first();

        LoanPayment::query()->create([
            'loan_id' => $completedLoan->id,
            'user_id' => $irfan->id,
            'payment_method_id' => $createdMethods->first()->id,
            'transaction_id' => 'TXN-88221100',
            'screenshot_path' => $demoPath,
            'status' => PaymentStatus::Completed,
            'admin_notes' => 'Verified by admin.',
            'submitted_at' => now()->subDays(10),
            'reviewed_at' => now()->subDays(9),
        ]);
    }
}
