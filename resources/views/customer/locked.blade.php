<x-layouts.guest :title="$appName">
    <div class="text-center">
        <span class="wallet-mark mx-auto">
            <x-wallet-mark />
        </span>
        <h1 class="mt-5 text-3xl font-extrabold text-brand">{{ $appName }}</h1>
        <p class="mt-2 text-sm text-muted">Create your secure gateway to growth</p>
        @if (session('error'))
            <div class="mt-6 rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif
        <p class="mt-8 text-sm text-muted">Sign in with the phone number on your customer account.</p>
    </div>
</x-layouts.guest>
