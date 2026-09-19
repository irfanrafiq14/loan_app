<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'app_name',
        'token_hash',
        'expires_at',
        'used_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function isValid(): bool
    {
        return ! $this->isExpired() && ! $this->isRevoked() && ! $this->isUsed();
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->isRevoked() => 'Revoked',
            $this->isUsed() => 'Used',
            $this->isExpired() => 'Expired',
            default => 'Active',
        };
    }
}
