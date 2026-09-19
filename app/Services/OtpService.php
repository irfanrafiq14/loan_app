<?php

namespace App\Services;

use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class OtpService
{
    public function issue(User $user, string $phone): OtpVerification
    {
        $code = $this->generateCode();

        $verification = OtpVerification::query()->create([
            'user_id' => $user->id,
            'phone' => $phone,
            'otp_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes((int) config('maxwallet.otp_ttl_minutes', 5)),
            'attempts' => 0,
        ]);

        session([
            'otp_verification_id' => $verification->id,
            'otp_demo_code' => $code,
        ]);

        return $verification;
    }

    public function verify(string $code): User
    {
        $verification = $this->pending();

        if ($verification->isExpired()) {
            throw new RuntimeException('expired');
        }

        if ($verification->hasExceededAttempts()) {
            throw new RuntimeException('locked');
        }

        $verification->increment('attempts');
        $verification->refresh();

        if (! Hash::check($code, $verification->otp_hash)) {
            throw new RuntimeException('invalid');
        }

        $verification->forceFill(['verified_at' => now()])->save();

        session()->forget(['otp_demo_code']);

        return $verification->user;
    }

    public function pending(): OtpVerification
    {
        $id = session('otp_verification_id');

        if (! $id) {
            throw new RuntimeException('missing');
        }

        $verification = OtpVerification::query()->with('user')->find($id);

        if (! $verification || $verification->isVerified()) {
            throw new RuntimeException('missing');
        }

        return $verification;
    }

    public function demoCode(): ?string
    {
        $code = session('otp_demo_code');

        if (! is_string($code) || $code === '') {
            return null;
        }

        try {
            $verification = $this->pending();
        } catch (RuntimeException) {
            return null;
        }

        if ($verification->isExpired()) {
            return null;
        }

        return $code;
    }

    private function generateCode(): string
    {
        if (app()->environment('testing')) {
            return (string) config('maxwallet.demo_otp', '1234');
        }

        $length = (int) config('maxwallet.otp_length', 4);
        $max = (10 ** $length) - 1;

        return str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }
}
