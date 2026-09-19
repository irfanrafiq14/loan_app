<?php

namespace Database\Factories;

use App\Models\LoginLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoginLink>
 */
class LoginLinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->customer(),
            'app_name' => 'MaxWallet',
            'token_hash' => hash('sha256', bin2hex(random_bytes(32))),
            'expires_at' => now()->addHours(24),
            'used_at' => null,
            'revoked_at' => null,
        ];
    }
}
