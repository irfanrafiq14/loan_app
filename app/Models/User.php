<?php

namespace App\Models;

use App\Enums\LoanStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Support\PhoneNumber;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'available_credit',
        'credit_min',
        'credit_max',
        'eligible_offer',
        'app_name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
            'available_credit' => 'decimal:2',
            'credit_min' => 'decimal:2',
            'credit_max' => 'decimal:2',
            'eligible_offer' => 'decimal:2',
        ];
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function loginLinks(): HasMany
    {
        return $this->hasMany(LoginLink::class);
    }

    public function otpVerifications(): HasMany
    {
        return $this->hasMany(OtpVerification::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::Customer;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public static function findActiveCustomerByPhone(string $phone): ?self
    {
        $digits = PhoneNumber::digits($phone);
        $local = str_starts_with($digits, '92') ? substr($digits, 2) : $digits;

        if (strlen($local) < 10) {
            return null;
        }

        return self::query()
            ->customers()
            ->where('status', UserStatus::Active)
            ->where(function (Builder $builder) use ($digits, $local) {
                $builder->where('phone', $digits)
                    ->orWhere('phone', 'like', '%'.$local);
            })
            ->first();
    }

    public function maskedPhone(): string
    {
        return PhoneNumber::mask($this->phone);
    }

    public function displayPhone(): string
    {
        return PhoneNumber::display($this->phone);
    }

    public function outstandingLoans(): HasMany
    {
        return $this->loans()
            ->whereIn('status', [LoanStatus::Pending, LoanStatus::Approved])
            ->whereDoesntHave('payments', fn ($query) => $query->where('status', PaymentStatus::Completed));
    }

    public function hasOutstandingDues(): bool
    {
        return $this->outstandingLoans()->exists();
    }

    public function activeLoans(): HasMany
    {
        return $this->loans()
            ->where('status', LoanStatus::Approved)
            ->whereDoesntHave('payments', fn ($query) => $query->where('status', PaymentStatus::Completed));
    }

    public function activeLoanAmount(): float
    {
        return (float) $this->activeLoans()->sum('amount');
    }

    public function pendingLoans(): HasMany
    {
        return $this->outstandingLoans();
    }

    public function pendingLoanAmount(): float
    {
        return (float) $this->pendingLoans()->sum('amount');
    }

    public function displayLoanAmount(): float
    {
        $pending = $this->pendingLoanAmount();

        return $pending > 0 ? $pending : $this->activeLoanAmount();
    }

    public function loanProgressBar(): array
    {
        $loans = $this->pendingLoans()->get();

        if ($loans->isEmpty()) {
            return [
                'amount' => 0.0,
                'min' => 0.0,
                'max' => 0.0,
                'percent' => 0.0,
                'label' => 'Pending loan amount',
                'count' => 0,
            ];
        }

        $amount = (float) $loans->sum('amount');
        $min = (float) $loans->min(fn (Loan $loan) => (float) ($loan->minimum_amount ?? 0));
        $max = (float) $loans->max(fn (Loan $loan) => (float) ($loan->maximum_amount ?: $loan->amount ?: 0));

        if ($max < $min) {
            [$min, $max] = [$max, $min];
        }

        if ($max < $amount) {
            $max = $amount;
        }

        $percent = 0.0;
        if ($max > $min) {
            $percent = (($amount - $min) / ($max - $min)) * 100;
        } elseif ($amount > 0) {
            $percent = 100.0;
        }

        return [
            'amount' => $amount,
            'min' => $min,
            'max' => $max,
            'percent' => max(0, min(100, $percent)),
            'label' => 'Pending loan amount',
            'count' => $loans->count(),
        ];
    }

    public function brandedName(): string
    {
        $name = trim((string) $this->app_name);

        if ($name !== '') {
            return $name;
        }

        return trim((string) $this->loginLinks()->latest('id')->value('app_name'));
    }

    public function greeting(): string
    {
        $hour = (int) now()->format('G');

        $period = match (true) {
            $hour >= 5 && $hour < 12 => 'Good Morning',
            $hour >= 12 && $hour < 17 => 'Good Afternoon',
            $hour >= 17 && $hour < 20 => 'Good Evening',
            default => 'Good Night',
        };

        return $period.', '.$this->name;
    }

    public function scopeCustomers(Builder $query): Builder
    {
        return $query->where('role', UserRole::Customer);
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', UserRole::Admin);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $digits = PhoneNumber::digits($term);

        return $query->where(function (Builder $builder) use ($term, $digits) {
            $builder->where('name', 'like', '%'.$term.'%');

            if ($digits !== '') {
                $builder->orWhere('phone', 'like', '%'.$digits.'%');
            }
        });
    }
}
