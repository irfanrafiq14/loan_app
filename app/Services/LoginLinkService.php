<?php

namespace App\Services;

use App\Models\LoginLink;
use App\Models\User;
use Illuminate\Support\Carbon;
use RuntimeException;

class LoginLinkService
{
    public function create(User $user, string $appName, ?Carbon $expiresAt = null): array
    {
        $plain = bin2hex(random_bytes(32));

        $link = LoginLink::query()->create([
            'user_id' => $user->id,
            'app_name' => $appName,
            'token_hash' => hash('sha256', $plain),
            'expires_at' => $expiresAt ?? now()->addHours((int) config('maxwallet.login_link_ttl_hours', 24)),
        ]);

        $user->forceFill(['app_name' => $appName])->save();

        return [
            'link' => $link,
            'token' => $plain,
            'url' => route('access.show', ['token' => $plain], true),
        ];
    }

    public function find(string $token): ?LoginLink
    {
        return LoginLink::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $token))
            ->first();
    }

    public function resolve(string $token): LoginLink
    {
        $link = $this->find($token);

        if (! $link) {
            throw new RuntimeException('invalid');
        }

        if ($link->isRevoked()) {
            throw new RuntimeException('revoked');
        }

        if ($link->isUsed()) {
            throw new RuntimeException('used');
        }

        if ($link->isExpired()) {
            throw new RuntimeException('expired');
        }

        if (! $link->user || ! $link->user->isCustomer() || ! $link->user->isActive()) {
            throw new RuntimeException('unavailable');
        }

        return $link;
    }

    public function markUsed(LoginLink $link): void
    {
        $link->forceFill(['used_at' => now()])->save();
    }

    public function revoke(LoginLink $link): void
    {
        $link->forceFill(['revoked_at' => now()])->save();
    }
}
