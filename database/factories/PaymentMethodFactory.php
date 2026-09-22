<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(\App\Models\PaymentMethod::catalog()),
            'account_number' => fake()->numerify('03#########'),
            'instructions' => 'Transfer the loan amount, then submit your transaction ID and screenshot.',
            'is_active' => true,
            'sort_order' => 1,
        ];
    }
}
