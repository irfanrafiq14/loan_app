<?php

namespace App\Support;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;

class AppBrand
{
    public const SESSION_KEY = 'customer_app_name';

    public const COOKIE_KEY = 'customer_app_name';

    public const USER_SESSION_KEY = 'customer_resume_user_id';

    public const USER_COOKIE_KEY = 'customer_resume_user';

    public static function name(): string
    {
        if (app()->bound('request') && request()->attributes->has('resolved_app_brand')) {
            return (string) request()->attributes->get('resolved_app_brand');
        }

        $name = self::resolveName();

        if (app()->bound('request')) {
            request()->attributes->set('resolved_app_brand', $name);
        }

        return $name;
    }

    private static function resolveName(): string
    {
        foreach ([
            session(self::SESSION_KEY),
            request()->cookie(self::COOKIE_KEY),
        ] as $candidate) {
            $name = trim((string) $candidate);

            if ($name !== '') {
                return $name;
            }
        }

        $user = auth()->user();

        if ($user instanceof User && $user->isCustomer()) {
            $name = $user->brandedName();

            if ($name !== '') {
                self::remember($name);

                return $name;
            }
        }

        $resume = self::resumeUser();

        if ($resume) {
            $name = $resume->brandedName();

            if ($name !== '') {
                self::remember($name);

                return $name;
            }
        }

        return (string) config('app.name', 'MaxWallet');
    }

    public static function remember(?string $name): void
    {
        $name = trim((string) $name);

        if ($name === '') {
            return;
        }

        session([self::SESSION_KEY => $name]);
        cookie()->queue(cookie(self::COOKIE_KEY, $name, 60 * 24 * 30));

        if (app()->bound('request')) {
            request()->attributes->set('resolved_app_brand', $name);
        }
    }

    public static function rememberClient(User $user, ?string $appName = null): void
    {
        $name = trim((string) ($appName ?: $user->brandedName()));

        if ($name !== '') {
            self::remember($name);

            if ($user->isCustomer() && $user->app_name !== $name) {
                $user->forceFill(['app_name' => $name])->save();
            }
        }

        session([self::USER_SESSION_KEY => $user->id]);
        cookie()->queue(cookie(self::USER_COOKIE_KEY, (string) $user->id, 60 * 24 * 30));
    }

    public static function resumeUser(): ?User
    {
        $id = session(self::USER_SESSION_KEY) ?: request()->cookie(self::USER_COOKIE_KEY);

        if (! $id) {
            return null;
        }

        return User::query()
            ->customers()
            ->where('status', UserStatus::Active)
            ->whereKey($id)
            ->first();
    }

    public static function hasClientBrand(): bool
    {
        return trim((string) session(self::SESSION_KEY)) !== ''
            || trim((string) request()->cookie(self::COOKIE_KEY)) !== ''
            || self::resumeUser() !== null;
    }

    public static function forgetClient(): void
    {
        if (app()->bound('session')) {
            session()->forget([
                self::SESSION_KEY,
                self::USER_SESSION_KEY,
                'client_resume_login',
                'otp_verification_id',
                'otp_demo_code',
                'access_token',
                'access_link_id',
            ]);
        }

        Cookie::queue(Cookie::forget(self::COOKIE_KEY));
        Cookie::queue(Cookie::forget(self::USER_COOKIE_KEY));

        if (app()->bound('request')) {
            request()->cookies->remove(self::COOKIE_KEY);
            request()->cookies->remove(self::USER_COOKIE_KEY);
            request()->attributes->remove('resolved_app_brand');
        }
    }

    public static function initial(?string $name = null): string
    {
        $name = trim((string) ($name ?? self::name()));
        $letter = $name === '' ? 'A' : mb_substr($name, 0, 1);

        return mb_strtoupper($letter);
    }

    public static function faviconHref(?string $name = null): string
    {
        $letter = htmlspecialchars(self::initial($name), ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
            .'<rect width="64" height="64" rx="16" fill="#0EA5E9"/>'
            .'<text x="32" y="43" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="34" font-weight="800" fill="#ffffff">'
            .$letter
            .'</text></svg>';

        return 'data:image/svg+xml,'.rawurlencode($svg);
    }
}
