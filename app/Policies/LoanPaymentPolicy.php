<?php

namespace App\Policies;

use App\Models\LoanPayment;
use App\Models\User;

class LoanPaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isCustomer();
    }

    public function view(User $user, LoanPayment $payment): bool
    {
        return $user->isAdmin() || ($user->isCustomer() && $payment->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->isCustomer() && $user->isActive();
    }

    public function review(User $user, LoanPayment $payment): bool
    {
        return $user->isAdmin() && $payment->isPending();
    }

    public function viewScreenshot(User $user, LoanPayment $payment): bool
    {
        return $user->isAdmin();
    }
}
