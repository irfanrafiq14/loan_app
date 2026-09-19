@php $knownBrand = $customer || \App\Support\AppBrand::hasClientBrand(); @endphp
<x-layouts.guest :title="$knownBrand ? 'Welcome · '.$appName : 'Sign in'">
    <div class="flex min-h-screen flex-col px-6 py-10">
        <div class="mt-8 flex flex-col items-center text-center">
            <div class="mb-5 flex h-20 w-20 items-center justify-center rounded-[28px] brand-gradient text-white shadow-lg">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8.5A2.5 2.5 0 0 1 5.5 6H20v12H5.5A2.5 2.5 0 0 1 3 15.5v-7Zm14 3.5h.01"/></svg>
            </div>
            <h1 class="text-3xl font-extrabold text-brand">{{ $knownBrand ? $appName : 'Welcome' }}</h1>
            <p class="mt-2 text-sm text-muted">Create your secure gateway to growth</p>
        </div>

        <div class="mt-10">
            @if ($customer)
                <h2 class="text-xl font-bold">Welcome, {{ $customer->name }}</h2>
                <p class="mt-1 text-sm text-muted">Enter your phone number to continue.</p>
            @else
                <h2 class="text-xl font-bold">Sign in</h2>
                <p class="mt-1 text-sm text-muted">Enter the phone number on your customer account to open your app on this device.</p>
            @endif
        </div>

        @if (session('error'))
            <div class="mt-6 rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('client.login.phone') }}" class="mt-8 space-y-5">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-semibold">Phone number</label>
                <x-phone-field />
                @error('country_code') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                @error('phone') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <button class="w-full rounded-2xl brand-gradient py-3.5 text-sm font-bold text-white shadow-md">Continue</button>
        </form>

        <p class="mt-auto pt-10 text-center text-xs text-muted">
            @if ($knownBrand)
                Need help? Contact {{ $appName }} support from your administrator.
            @else
                Need help? Ask your administrator for the phone number on your account.
            @endif
        </p>
    </div>
</x-layouts.guest>
