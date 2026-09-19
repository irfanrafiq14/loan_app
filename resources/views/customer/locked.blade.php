<x-layouts.guest :title="$appName">
    <div class="flex min-h-screen flex-col items-center justify-center px-6 py-10 text-center">
        <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-[28px] brand-gradient text-white shadow-lg">
            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8.5A2.5 2.5 0 0 1 5.5 6H20v12H5.5A2.5 2.5 0 0 1 3 15.5v-7Zm14 3.5h.01"/></svg>
        </div>
        <h1 class="text-3xl font-extrabold text-brand">{{ $appName }}</h1>
        <p class="mt-2 max-w-xs text-sm text-muted">Create your secure gateway to growth</p>
        @if (session('error'))
            <div class="mt-6 w-full rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif
        <p class="mt-8 text-sm text-muted">Open the private login link sent by your administrator to continue.</p>
    </div>
</x-layouts.guest>
