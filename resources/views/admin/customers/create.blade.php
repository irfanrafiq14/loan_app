<x-layouts.admin title="Create customer" heading="Create customer">
    <form method="POST" action="{{ route('admin.customers.store') }}" class="max-w-xl space-y-4 rounded-3xl bg-white p-6 shadow-sm">
        @csrf
        <div>
            <label class="mb-2 block text-sm font-semibold">Full name</label>
            <input name="name" value="{{ old('name') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold">Phone number</label>
            <x-phone-field />
            @error('country_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold">App name</label>
            <input name="app_name" value="{{ old('app_name') }}" placeholder="EasyCash" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
            <p class="mt-1 text-xs text-muted">Shown in the customer app after they sign in. Send only the base app URL — they sign in with phone and OTP. Do not add the app name to the link.</p>
            @error('app_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold">Payment link</label>
            <input name="payment_link" value="{{ old('payment_link') }}" placeholder="778028656@omni" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
            <p class="mt-1 text-xs text-muted">This customer copies this UPI ID or URL on the payment page. Each customer can have a different link.</p>
            @error('payment_link') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold">Status</label>
            <select name="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-semibold">Available credit (₹)</label>
                <input name="available_credit" type="number" value="{{ old('available_credit', 34500) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold">Eligible offer (₹)</label>
                <input name="eligible_offer" type="number" value="{{ old('eligible_offer', 50000) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold">Minimum amount (₹)</label>
                <input name="credit_min" type="number" value="{{ old('credit_min', 2000) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold">Maximum amount (₹)</label>
                <input name="credit_max" type="number" value="{{ old('credit_max', 34500) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
            </div>
        </div>
        <button class="rounded-2xl brand-gradient px-5 py-3 text-sm font-bold text-white">Save customer</button>
    </form>
</x-layouts.admin>
