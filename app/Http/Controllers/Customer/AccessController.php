<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\AccessPhoneRequest;
use App\Http\Requests\Customer\VerifyOtpRequest;
use App\Models\User;
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
        private readonly OtpService $otp,
    ) {
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
            'phone' => PhoneNumber::display($verification->phone ?: $verification->user->phone),
        ]);
    }

    public function resend(): JsonResponse
    {
        try {
            $pending = $this->otp->pending();
        } catch (RuntimeException) {
            return response()->json(['ok' => false], 404);
        }

        $this->otp->issue($pending->user, $pending->phone);

        return response()->json(['ok' => true]);
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

        Auth::login($user);
        $request->session()->regenerate();
        AppBrand::rememberClient($user, $user->brandedName());
        $request->session()->forget(['otp_verification_id', 'otp_demo_code', 'client_resume_login']);

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

        $appName = AppBrand::captureFromRequest();

        return view('customer.login', [
            'appName' => $appName,
            'showWelcome' => false,
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
}
