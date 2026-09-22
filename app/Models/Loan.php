<?php

namespace App\Models;

use App\Enums\LoanStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'reference_code',
        'amount',
        'total_due',
        'minimum_amount',
        'maximum_amount',
        'loan_date',
        'due_date',
        'description',
        'payment_instructions',
        'status',
        'is_featured',
        'featured_image',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'total_due' => 'decimal:2',
            'minimum_amount' => 'decimal:2',
            'maximum_amount' => 'decimal:2',
            'loan_date' => 'date',
            'due_date' => 'date',
            'status' => LoanStatus::class,
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Loan $loan) {
            if (! filled($loan->reference_code)) {
                $loan->reference_code = static::uniqueReferenceCode();
            }

            if ($loan->total_due === null && $loan->amount !== null) {
                $loan->total_due = $loan->amount;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    public static function uniqueReferenceCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::query()->where('reference_code', $code)->exists());

        return $code;
    }

    public function reference(): string
    {
        $code = strtoupper(trim((string) $this->reference_code));

        if ($code === '') {
            $code = str_pad((string) $this->id, 8, '0', STR_PAD_LEFT);
        }

        return '#'.$code;
    }

    public function totalDueAmount(): float
    {
        if ($this->total_due === null || $this->total_due === '') {
            return (float) $this->amount;
        }

        return (float) $this->total_due;
    }

    public function hasFeaturedImage(): bool
    {
        return filled($this->featured_image);
    }

    public function featuredImageUrl(): string
    {
        return $this->hasFeaturedImage()
            ? route('featured-loans.image', $this)
            : asset('images/featured-placeholder.svg');
    }

    public function hasPendingPayment(): bool
    {
        return $this->payments()->where('status', PaymentStatus::Pending)->exists();
    }

    public function hasCompletedPayment(): bool
    {
        return $this->payments()->where('status', PaymentStatus::Completed)->exists();
    }

    public function canAcceptPayment(): bool
    {
        return in_array($this->status, [LoanStatus::Pending, LoanStatus::Approved], true)
            && ! $this->hasPendingPayment()
            && ! $this->hasCompletedPayment();
    }

    public function canBeDeleted(): bool
    {
        return ! $this->payments()->exists();
    }

    public function latestPayment(): ?LoanPayment
    {
        return $this->payments()->latest('id')->first();
    }

    public function scopeAssigned(Builder $query): Builder
    {
        return $query->whereNotNull('user_id');
    }

    public function scopeFeaturedOffers(Builder $query): Builder
    {
        return $query->where('is_featured', true)
            ->whereIn('status', [LoanStatus::Pending, LoanStatus::Approved]);
    }

    public function scopeForCustomer(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (! $status) {
            return $query;
        }

        return $query->where('status', $status);
    }
}
