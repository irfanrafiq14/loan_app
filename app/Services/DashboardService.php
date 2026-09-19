<?php

namespace App\Services;

use App\Enums\LoanStatus;
use App\Enums\PaymentStatus;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\User;

class DashboardService
{
    public function stats(): array
    {
        return [
            'total_customers' => User::query()->customers()->count(),
            'active_loans' => Loan::query()->assigned()->whereIn('status', [LoanStatus::Pending, LoanStatus::Approved])->count(),
            'pending_payments' => LoanPayment::query()->where('status', PaymentStatus::Pending)->count(),
            'completed_payments' => LoanPayment::query()->where('status', PaymentStatus::Completed)->count(),
            'total_loan_amount' => (float) Loan::query()->assigned()->sum('amount'),
            'total_amount_paid' => (float) Loan::query()
                ->assigned()
                ->whereHas('payments', fn ($query) => $query->where('status', PaymentStatus::Completed))
                ->sum('amount'),
        ];
    }
}
