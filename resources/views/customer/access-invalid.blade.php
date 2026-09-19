<x-layouts.guest :title="'Link unavailable · '.$appName">
    <div class="flex min-h-screen flex-col items-center justify-center px-6 text-center">
        <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-red-50 text-red-600">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.3 4.2 2.5 18a2 2 0 0 0 1.7 3h15.6a2 2 0 0 0 1.7-3L13.7 4.2a2 2 0 0 0-3.4 0Z"/></svg>
        </div>
        <h1 class="text-2xl font-extrabold">This login link is unavailable</h1>
        <p class="mt-3 text-sm text-muted">
            @switch($reason)
                @case('expired') This secure link has expired. Ask your administrator for a new one. @break
                @case('used') This link has already been used. Ask your administrator for a new one. @break
                @case('revoked') This link was revoked by an administrator. @break
                @default This link is invalid or no longer active.
            @endswitch
        </p>
        <a href="{{ route('client.login') }}" class="mt-8 rounded-2xl brand-gradient px-6 py-3 text-sm font-bold text-white">Back to {{ $appName }}</a>
    </div>
</x-layouts.guest>
