<?php

namespace App\Models;

use App\Enums\LoanStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'amount',
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
            'minimum_amount' => 'decimal:2',
            'maximum_amount' => 'decimal:2',
            'loan_date' => 'date',
            'due_date' => 'date',
            'status' => LoanStatus::class,
            'is_featured' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function reference(): string
    {
        return 'MW-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
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
