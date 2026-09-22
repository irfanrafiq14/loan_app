<?php

return [
    'otp_ttl_minutes' => (int) env('MAXWALLET_OTP_MINUTES', 5),
    'otp_max_attempts' => (int) env('MAXWALLET_OTP_MAX_ATTEMPTS', 5),
    'otp_length' => 4,
    'demo_otp' => env('MAXWALLET_DEMO_OTP', '1234'),
];
