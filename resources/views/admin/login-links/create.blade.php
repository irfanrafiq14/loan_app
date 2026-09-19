<x-layouts.admin title="Create login link" heading="Create login link">
    <form method="POST" action="{{ route('admin.login-links.store') }}" class="max-w-xl space-y-4 rounded-3xl bg-white p-6 shadow-sm">
        @csrf
        <div>
            <label class="mb-2 block text-sm font-semibold">Customer</label>
            <select name="user_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
                <option value="">Select customer</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected($selected === $customer->id)>{{ $customer->name }} · {{ $customer->displayPhone() }}</option>
                @endforeach
            </select>
            @error('user_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold">App name</label>
            <input name="app_name" value="{{ old('app_name', 'MaxWallet') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
        </div>
        <p class="text-sm text-muted">A unique expiring link will be generated. Only a hash of the token is stored. The raw URL is shown once so you can copy it.</p>
        <button class="rounded-2xl brand-gradient px-5 py-3 text-sm font-bold text-white">Generate link</button>
    </form>
</x-layouts.admin>
