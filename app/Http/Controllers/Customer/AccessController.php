<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\AccessPhoneRequest;
use App\Http\Requests\Customer\VerifyOtpRequest;
use App\Models\User;
use App\Services\LoginLinkService;
use App\Services\OtpService;
use App\Support\AppBrand;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;

class AccessController extends Controller
{
    public function __construct(
        private readonly LoginLinkService $loginLinks,
        private readonly OtpService $otp,
    ) {
    }

    public function show(string $token): View|RedirectResponse
    {
        try {
            $link = $this->loginLinks->resolve($token);
        } catch (RuntimeException $exception) {
            return $this->redirectExpiredOrUsedLink($token, $exception->getMessage());
        }

        AppBrand::rememberClient($link->user, $link->app_name);

        session([
            'access_token' => $token,
            'access_link_id' => $link->id,
        ]);

        return view('customer.access', compact('link'));
    }

    public function submitPhone(AccessPhoneRequest $request, string $token): RedirectResponse
    {
        try {
            $link = $this->loginLinks->resolve($token);
        } catch (RuntimeException $exception) {
            return $this->redirectExpiredOrUsedLink($token, $exception->getMessage());
        }

        $phone = PhoneNumber::normalize(
            $request->string('country_code')->toString(),
            $request->string('phone')->toString()
        );

        if (! PhoneNumber::matches($phone, $link->user->phone)) {
            return back()
                ->withErrors(['phone' => 'This phone number does not match your '.AppBrand::name().' account.'])
                ->withInput();
        }

        $this->otp->issue($link->user, $phone);
        AppBrand::remember($link->app_name);

        session([
            'access_token' => $token,
            'access_link_id' => $link->id,
        ]);

        return redirect()->route('verify-otp.show');
    }

    public function showOtp(): View|RedirectResponse
    {
        try {
            $verification = $this->otp->pending();
        } catch (RuntimeException) {
            return redirect()->route('client.login');
        }

        return view('customer.otp', [
            'customer' => $verification->user,
        ]);
    }

    public function autofill(): JsonResponse
    {
        $code = $this->otp->demoCode();

        if (! $code) {
            return response()->json(['otp' => null], 404);
        }

        return response()->json(['otp' => $code]);
    }

    public function verify(VerifyOtpRequest $request): RedirectResponse
    {
        try {
            $user = $this->otp->verify($request->string('otp')->toString());
        } catch (RuntimeException $exception) {
            $message = match ($exception->getMessage()) {
                'expired' => 'Your verification code has expired. Sign in again with your phone number.',
                'locked' => 'Too many attempts. Sign in again with your phone number.',
                'invalid' => 'The verification code is incorrect.',
                default => 'We could not verify this code. Sign in again with your phone number.',
            };

            if ($exception->getMessage() === 'invalid') {
                return back()->withErrors(['otp' => $message]);
            }

            return redirect()->route('client.login')->with('error', $message);
        }

        if (session('client_resume_login')) {
            Auth::login($user);
            $request->session()->regenerate();
            AppBrand::rememberClient($user, $user->brandedName());
            $request->session()->forget(['otp_verification_id', 'otp_demo_code', 'client_resume_login']);

            return redirect()->route('home');
        }

        try {
            $link = $this->loginLinks->resolve((string) session('access_token'));
        } catch (RuntimeException) {
            return redirect()->route('client.login')->with('error', 'This login link is no longer valid.');
        }

        $this->loginLinks->markUsed($link);
        Auth::login($user);
        $request->session()->regenerate();
        AppBrand::rememberClient($user, $link->app_name);
        $request->session()->forget(['otp_verification_id', 'otp_demo_code', 'access_token', 'access_link_id']);

        return redirect()->route('home');
    }

    public function showClientLogin(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if (Auth::check() && Auth::user()->isCustomer()) {
            return redirect()->route('home');
        }

        $customer = AppBrand::resumeUser();

        return view('customer.login', [
            'customer' => $customer,
            'appName' => AppBrand::name(),
        ]);
    }

    public function submitClientPhone(AccessPhoneRequest $request): RedirectResponse
    {
        $phone = PhoneNumber::normalize(
            $request->string('country_code')->toString(),
            $request->string('phone')->toString()
        );

        $customer = User::findActiveCustomerByPhone($phone);

        if (! $customer) {
            return back()
                ->withErrors(['phone' => 'No customer account was found for this phone number.'])
                ->withInput();
        }

        $this->otp->issue($customer, $phone);
        AppBrand::rememberClient($customer, $customer->brandedName());
        session(['client_resume_login' => true]);

        return redirect()->route('verify-otp.show');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        AppBrand::forgetClient();

        return redirect()->route('client.login');
    }

    private function redirectExpiredOrUsedLink(string $token, string $reason): RedirectResponse
    {
        $link = $this->loginLinks->find($token);

        if ($link?->user?->isCustomer() && $link->user->isActive()) {
            AppBrand::rememberClient($link->user, $link->app_name);
        }

        $message = match ($reason) {
            'expired' => 'This login link has expired. Sign in below to continue.',
            'used' => 'This login link was already used. Sign in below to continue.',
            'revoked' => 'This login link was revoked. Sign in below to continue.',
            default => 'This login link is no longer valid. Sign in below to continue.',
        };

        return redirect()->route('client.login')->with('error', $message);
    }
}
