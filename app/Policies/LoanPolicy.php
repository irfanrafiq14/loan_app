<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isCustomer();
    }

    public function view(User $user, Loan $loan): bool
    {
        return $user->isAdmin() || ($user->isCustomer() && $loan->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Loan $loan): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Loan $loan): bool
    {
        return $user->isAdmin() && $loan->canBeDeleted();
    }

    public function apply(User $user, Loan $loan): bool
    {
        return $user->isCustomer()
            && $user->isActive()
            && in_array($loan->status, [\App\Enums\LoanStatus::Pending, \App\Enums\LoanStatus::Approved], true);
    }

    public function pay(User $user, Loan $loan): bool
    {
        return $user->isCustomer()
            && $user->isActive()
            && $loan->user_id === $user->id
            && $loan->canAcceptPayment();
    }
}
