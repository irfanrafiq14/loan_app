<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '92'.fake()->unique()->numerify('##########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Customer,
            'status' => UserStatus::Active,
            'available_credit' => 34500,
            'credit_min' => 2000,
            'credit_max' => 34500,
            'eligible_offer' => 50000,
            'app_name' => fake()->optional()->randomElement(['EasyCash', 'testapp']),
            'payment_link' => fake()->optional()->bothify('########@upi'),
            'support_email' => 'support@example.com',
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::Admin,
            'phone' => null,
        ]);
    }

    public function customer(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::Customer,
            'password' => null,
            'email' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => UserStatus::Inactive,
        ]);
    }
}
