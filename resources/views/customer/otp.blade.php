<x-layouts.guest :title="'Verify · '.$appName">
    <div class="text-center" x-data="otpForm" x-init="start()">
        <span class="wallet-mark mx-auto">
            <x-wallet-mark />
        </span>
        <h1 class="mt-5 text-2xl font-extrabold text-brand">Verification Code</h1>
        <p class="mt-2 text-sm text-muted">
            Welcome back, {{ $customer->name }}. Please enter the 4-digit code sent to
            <span class="font-bold text-ink">{{ $phone }}</span>
        </p>

        <form method="POST" action="{{ route('verify-otp.verify') }}" class="mt-8" x-ref="form">
            @csrf
            <input type="hidden" name="otp" x-model="otp">
            <div class="flex justify-center gap-3">
                <template x-for="(digit, index) in digits" :key="index">
                    <input type="text" maxlength="1" inputmode="numeric" placeholder="•"
                           class="otp-box rounded-2xl text-center text-xl font-bold focus:border-brand focus:bg-white focus:outline-none"
                           x-model="digits[index]"
                           @input="onInput(index, $event)"
                           @keydown.backspace="onBackspace(index, $event)">
                </template>
            </div>
            @error('otp') <p class="mt-4 text-sm text-red-600">{{ $message }}</p> @enderror
            <p class="mt-5 text-sm text-muted">We are fetching the code automatically — you do not need to type anything.</p>
            <p class="mt-2 text-xs text-muted" x-show="fetching">Fetching your verification code…</p>

            <div class="mt-5 inline-flex items-center gap-2 rounded-full bg-brand-soft px-3 py-1.5 text-sm font-bold text-brand">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l2.5 1.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span x-text="clock()"></span>
            </div>

            <div class="mt-4">
                <button type="button" class="text-sm font-semibold text-brand" @click="resend()">Resend OTP</button>
            </div>

            <button class="btn-primary mt-6">Verify & Proceed</button>
        </form>

        <p class="mt-8 text-xs leading-5 text-muted">If you're having trouble receiving the code, contact the {{ $appName }} support team.</p>
        @if ($supportGmailUrl)
            <a href="{{ $supportGmailUrl }}" target="_blank" rel="noopener noreferrer" class="btn-primary mt-4">
                Support
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5M15 3h6m0 0v6m0-6L10 14"/></svg>
            </a>
        @endif
    </div>
</x-layouts.guest>
