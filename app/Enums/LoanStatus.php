<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Completed = 'completed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Completed => 'Paid',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-brand-soft text-brand',
            self::Approved => 'bg-brand-soft text-brand',
            self::Completed => 'bg-white text-brand ring-1 ring-brand/30',
            self::Rejected => 'bg-red-100 text-red-800',
        };
    }

    public function customerLabel(): string
    {
        return $this === self::Completed ? 'Completed' : 'Pending';
    }
}
