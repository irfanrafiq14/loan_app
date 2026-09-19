<x-layouts.guest :title="'Verify · '.$appName">
    <div class="flex min-h-screen flex-col px-6 py-10"
         x-data="otpForm"
         x-init="start()">
        <div class="mt-6 text-center">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl brand-gradient text-white">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 11V8a4 4 0 1 0-8 0v3m16 0V8a4 4 0 0 0-8 0v3M4 11h16v9a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1z"/></svg>
            </div>
            <h1 class="text-2xl font-extrabold">{{ $appName }}</h1>
            <p class="mt-2 text-sm text-muted">Welcome back, {{ $customer->name }}. Enter the 4-digit code to continue.</p>
        </div>

        <form method="POST" action="{{ route('verify-otp.verify') }}" class="mt-10" x-ref="form">
            @csrf
            <input type="hidden" name="otp" x-model="otp">
            <div class="flex justify-center gap-2">
                <template x-for="(digit, index) in digits" :key="index">
                    <input type="text" maxlength="1" inputmode="numeric" class="otp-box rounded-2xl border border-slate-200 text-center text-xl font-bold focus:border-brand focus:outline-none"
                           x-model="digits[index]"
                           @input="onInput(index, $event)"
                           @keydown.backspace="onBackspace(index, $event)">
                </template>
            </div>
            @error('otp') <p class="mt-4 text-center text-sm text-red-600">{{ $message }}</p> @enderror
            <p class="mt-6 text-center text-sm text-muted">Code expires in <span class="font-bold text-brand" x-text="timer + 's'"></span></p>
            <p class="mt-2 text-center text-xs text-muted" x-show="fetching">Fetching your verification code…</p>
            <button class="mt-8 w-full rounded-2xl brand-gradient py-3.5 text-sm font-bold text-white">Verify and continue</button>
        </form>
    </div>
</x-layouts.guest>
