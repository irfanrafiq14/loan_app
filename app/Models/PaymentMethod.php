<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'account_number',
        'instructions',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function paymentLink(): string
    {
        return trim((string) $this->account_number);
    }

    /**
     * @return list<string>
     */
    public static function catalog(): array
    {
        return [
            'Google Pay',
            'PhonePe',
            'Paytm',
            'UPI',
        ];
    }

    public function brandKey(): string
    {
        $name = strtolower($this->name);

        return match (true) {
            str_contains($name, 'paytm') => 'paytm',
            str_contains($name, 'phonepe'), str_contains($name, 'phone pe') => 'phonepe',
            str_contains($name, 'google'), str_contains($name, 'gpay') => 'gpay',
            str_contains($name, 'upi') => 'upi',
            default => 'default',
        };
    }

    public function logoUrl(): string
    {
        $file = $this->brandKey() === 'default'
            ? 'default.svg'
            : $this->brandKey().'.png';

        return asset('images/payments/'.$file);
    }

    public static function sharedLink(): string
    {
        $link = static::query()
            ->whereIn('name', static::catalog())
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '')
            ->value('account_number');

        if ($link) {
            return trim((string) $link);
        }

        return trim((string) static::query()->value('account_number'));
    }

    public static function syncSharedLink(?string $link = null): void
    {
        $link = trim((string) ($link ?? static::sharedLink()));

        foreach (static::catalog() as $index => $name) {
            static::query()->updateOrCreate(
                ['name' => $name],
                [
                    'account_number' => $link,
                    'instructions' => 'Copy the payment link, pay in this app, then submit the transaction ID and screenshot.',
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }

        static::query()
            ->whereNotIn('name', static::catalog())
            ->update(['is_active' => false]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function catalogMethods()
    {
        static::syncSharedLink();

        return static::query()
            ->whereIn('name', static::catalog())
            ->active()
            ->get()
            ->sortBy(fn (self $method) => array_search($method->name, static::catalog(), true))
            ->values();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
