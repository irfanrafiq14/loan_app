<x-layouts.guest title="Welcome">
    <div class="text-center">
        <span class="wallet-mark mx-auto">
            <x-wallet-mark />
        </span>
        <h1 class="mt-5 text-3xl font-extrabold text-brand" x-text="name !== '' ? name : 'Welcome'">{{ filled($appName) ? $appName : 'Welcome' }}</h1>
        <p class="mt-2 text-sm text-muted">Create your secure gateway to growth</p>
    </div>

    @if (session('error'))
        <div class="mt-5 rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('client.login.phone') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Phone number</label>
            <x-phone-field />
            @error('country_code') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            @error('phone') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <button class="btn-primary">
            Continue
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5-5 5M6 12h12"/></svg>
        </button>
    </form>

    <p class="mt-8 text-center text-xs leading-5 text-muted">
        Having trouble logging into your account? Contact the
        <span x-show="name !== ''" x-text="name"></span>
        support team.
    </p>
    @if ($supportGmailUrl)
        <a href="{{ $supportGmailUrl }}" target="_blank" rel="noopener noreferrer" class="btn-primary mt-4">
            Support
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5M15 3h6m0 0v6m0-6L10 14"/></svg>
        </a>
    @endif
</x-layouts.guest>
