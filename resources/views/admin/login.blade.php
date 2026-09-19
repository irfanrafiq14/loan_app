<x-layouts.guest title="Admin login · MaxWallet">
    <div class="flex min-h-screen flex-col justify-center px-6 py-10">
        <div class="text-center">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl brand-gradient text-white">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8.5A2.5 2.5 0 0 1 5.5 6H20v12H5.5A2.5 2.5 0 0 1 3 15.5v-7Z"/></svg>
            </div>
            <h1 class="text-2xl font-extrabold text-brand">MaxWallet Admin</h1>
            <p class="mt-1 text-sm text-muted">Sign in to manage customers, loans, and payments.</p>
        </div>
        <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-4">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-semibold">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-brand" required>
                @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold">Password</label>
                <input type="password" name="password" class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-brand" required>
            </div>
            <button class="w-full rounded-2xl brand-gradient py-3.5 text-sm font-bold text-white">Sign in</button>
        </form>
    </div>
</x-layouts.guest>
