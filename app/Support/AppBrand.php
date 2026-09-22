<?php

namespace App\Support;

use App\Enums\UserStatus;
use App\Models\OtpVerification;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;

class AppBrand
{
    public const SESSION_KEY = 'customer_app_name';

    public const COOKIE_KEY = 'customer_app_name';

    public const USER_SESSION_KEY = 'customer_resume_user_id';

    public const USER_COOKIE_KEY = 'customer_resume_user';

    public const WELCOME_SESSION_KEY = 'customer_app_welcomed';

    public const WELCOME_COOKIE_KEY = 'customer_app_welcomed';

    public const TOKEN_MIN_LENGTH = 7;

    public static function name(): string
    {
        if (app()->bound('request') && request()->attributes->has('resolved_app_brand')) {
            return self::sanitize((string) request()->attributes->get('resolved_app_brand'));
        }

        $name = self::resolveName();

        if (app()->bound('request')) {
            request()->attributes->set('resolved_app_brand', $name);
        }

        return $name;
    }

    public static function captureFromRequest(): ?string
    {
        $fromToken = self::nameFromRequestToken();

        if ($fromToken) {
            return $fromToken;
        }

        return self::name();
    }

    public static function nameFromRequestToken(): ?string
    {
        if (! app()->bound('request')) {
            return null;
        }

        $raw = (string) (request()->query('t') ?: request()->route('token') ?: '');
        $token = strtolower(preg_replace('/[^a-z0-9]/', '', $raw) ?? '');

        if (strlen($token) < self::TOKEN_MIN_LENGTH) {
            return null;
        }

        $customer = User::query()
            ->customers()
            ->where('status', UserStatus::Active)
            ->where('app_token', $token)
            ->first();

        $name = self::sanitize($customer?->brandedName());

        if ($name === '') {
            return null;
        }

        self::remember($name);

        return $name;
    }

    public static function clientUrl(?string $appName = null): string
    {
        return rtrim((string) config('app.url'), '/').'/';
    }

    public static function localClientUrl(): string
    {
        $appUrl = (string) config('app.url');
        $port = parse_url($appUrl, PHP_URL_PORT);
        $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'http';

        $base = $scheme.'://127.0.0.1';

        if ($port) {
            $base .= ':'.$port;
        }

        return $base.'/';
    }

    public static function brandedLocalUrl(User $customer): string
    {
        $base = self::localClientUrl();
        $token = $customer->appToken();

        return $base.'?t='.$token;
    }

    public static function customerForApp(?string $appName): ?User
    {
        $name = self::sanitize($appName);

        if ($name === '') {
            return null;
        }

        return User::query()
            ->customers()
            ->where('status', UserStatus::Active)
            ->whereRaw('LOWER(app_name) = ?', [mb_strtolower($name)])
            ->first();
    }

    public static function supportEmail(): ?string
    {
        $global = Setting::getValue('support_email');

        if ($global) {
            return $global;
        }

        $user = auth()->user();

        if ($user instanceof User && $user->isCustomer()) {
            $email = trim((string) $user->support_email);

            if ($email !== '') {
                return $email;
            }
        }

        $customer = self::customerForApp(self::name()) ?: self::resumeUser();
        $email = trim((string) ($customer?->support_email));

        return $email !== '' ? $email : null;
    }

    public static function gmailUrl(?string $email = null): ?string
    {
        $email = trim((string) ($email ?? self::supportEmail()));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $subject = self::name() !== '' ? self::name().' support' : 'Support';

        return 'https://mail.google.com/mail/?view=cm&fs=1&tf=1&to='.rawurlencode($email).'&su='.rawurlencode($subject);
    }

    private static function resolveName(): string
    {
        $user = auth()->user();

        if ($user instanceof User && $user->isCustomer()) {
            $name = self::sanitize($user->brandedName());

            if ($name !== '') {
                self::remember($name);

                return $name;
            }
        }

        $pending = self::pendingOtpUser();

        if ($pending) {
            $name = self::sanitize($pending->brandedName());

            if ($name !== '') {
                self::remember($name);

                return $name;
            }
        }

        return '';
    }

    public static function remember(?string $name): void
    {
        $name = self::sanitize($name);

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
        $name = self::sanitize($appName ?: $user->brandedName());

        if ($name !== '') {
            self::remember($name);

            if ($user->isCustomer() && $user->app_name !== $name) {
                $user->forceFill(['app_name' => $name])->save();
            }
        }

        session([self::USER_SESSION_KEY => $user->id]);
        cookie()->queue(cookie(self::USER_COOKIE_KEY, (string) $user->id, 60 * 24 * 30));
        self::markWelcomed($name);
    }

    public static function shouldShowWelcome(): bool
    {
        return false;
    }

    public static function hasBeenWelcomed(?string $appName = null): bool
    {
        $name = self::sanitize($appName ?? self::name());

        if ($name === '') {
            return false;
        }

        $session = self::sanitize((string) session(self::WELCOME_SESSION_KEY));
        $cookie = self::sanitize((string) request()->cookie(self::WELCOME_COOKIE_KEY));

        return strcasecmp($session, $name) === 0
            || strcasecmp($cookie, $name) === 0;
    }

    public static function markWelcomed(?string $appName = null): void
    {
        $name = self::sanitize($appName ?? self::name());

        if ($name === '') {
            return;
        }

        session([self::WELCOME_SESSION_KEY => $name]);
        cookie()->queue(cookie(self::WELCOME_COOKIE_KEY, $name, 60 * 24 * 30));
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
        return self::name() !== '';
    }

    public static function forgetIdentity(): void
    {
        if (app()->bound('session')) {
            session()->forget([
                self::USER_SESSION_KEY,
                self::WELCOME_SESSION_KEY,
                'client_resume_login',
                'otp_verification_id',
                'otp_demo_code',
                'access_token',
                'access_link_id',
            ]);
        }

        Cookie::queue(Cookie::forget(self::USER_COOKIE_KEY));
        Cookie::queue(Cookie::forget(self::WELCOME_COOKIE_KEY));

        if (app()->bound('request')) {
            request()->cookies->remove(self::USER_COOKIE_KEY);
            request()->cookies->remove(self::WELCOME_COOKIE_KEY);
        }
    }

    public static function forgetClient(): void
    {
        self::forgetIdentity();

        if (app()->bound('session')) {
            session()->forget([self::SESSION_KEY]);
        }

        Cookie::queue(Cookie::forget(self::COOKIE_KEY));

        if (app()->bound('request')) {
            request()->cookies->remove(self::COOKIE_KEY);
            request()->attributes->remove('resolved_app_brand');
        }
    }

    public static function initial(?string $name = null): string
    {
        $name = self::sanitize($name ?? self::name());

        if ($name === '') {
            return '';
        }

        return mb_strtoupper(mb_substr($name, 0, 1));
    }

    public static function faviconHref(?string $name = null): string
    {
        $letter = htmlspecialchars(self::initial($name), ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
            .'<rect width="64" height="64" rx="16" fill="#0EA5E9"/>';

        if ($letter !== '') {
            $svg .= '<text x="32" y="43" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="34" font-weight="800" fill="#ffffff">'
                .$letter
                .'</text>';
        }

        $svg .= '</svg>';

        return 'data:image/svg+xml,'.rawurlencode($svg);
    }

    public static function sanitize(?string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', strip_tags((string) $name)) ?? '');

        if ($name === '' || mb_strlen($name) > 80) {
            return '';
        }

        if (self::isReserved($name)) {
            return '';
        }

        return $name;
    }

    private static function pendingOtpUser(): ?User
    {
        if (! app()->bound('session')) {
            return null;
        }

        $id = session('otp_verification_id');

        if (! $id) {
            return null;
        }

        $verification = OtpVerification::query()->with('user')->find($id);

        if (! $verification || $verification->isVerified()) {
            return null;
        }

        $user = $verification->user;

        return $user instanceof User && $user->isCustomer() ? $user : null;
    }

    private static function isReserved(string $name): bool
    {
        $blocked = ['maxwallet', 'max wallet'];
        $config = strtolower(trim((string) config('app.name')));

        if ($config !== '') {
            $blocked[] = $config;
        }

        return in_array(strtolower($name), $blocked, true);
    }
}
