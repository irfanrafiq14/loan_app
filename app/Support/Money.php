<?php

namespace App\Support;

class Money
{
    public static function format(float|int|string|null $amount): string
    {
        return 'Rs. '.number_format((float) $amount, 0);
    }

    public static function formatExact(float|int|string|null $amount): string
    {
        return 'Rs. '.number_format((float) $amount, 2);
    }
}
