<x-layouts.guest :title="'Welcome · '.$link->app_name">
    <div class="text-center">
        <span class="wallet-mark mx-auto">
            <x-wallet-mark />
        </span>
        <h1 class="mt-5 text-3xl font-extrabold text-brand">{{ $link->app_name }}</h1>
        <p class="mt-2 text-sm text-muted">Create your secure gateway to growth</p>
        <p class="mt-4 text-sm font-semibold">Welcome, {{ $link->user->name }}</p>
    </div>

    <form method="POST" action="{{ route('access.phone', request()->route('token')) }}" class="mt-8 space-y-5">
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

    <p class="mt-8 text-center text-xs leading-5 text-muted">Having trouble logging into your account? Contact the {{ $link->app_name }} support team.</p>
</x-layouts.guest>
