<x-layouts.admin title="Edit customer" heading="Edit customer">
    <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="max-w-xl space-y-4 rounded-3xl bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <div>
            <label class="mb-2 block text-sm font-semibold">Full name</label>
            <input name="name" value="{{ old('name', $customer->name) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold">Phone number</label>
            <x-phone-field :phone="old('phone', \App\Support\PhoneNumber::localPart($customer->phone))" />
            @error('country_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold">App name</label>
            <input name="app_name" value="{{ old('app_name', $customer->app_name) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
            @error('app_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold">Payment link</label>
            <input name="payment_link" value="{{ old('payment_link', $customer->payment_link) }}" placeholder="778028656@omni" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
            <p class="mt-1 text-xs text-muted">Shown only to this customer on the payment page.</p>
            @error('payment_link') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold">Status</label>
            <select name="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                <option value="active" @selected($customer->status->value === 'active')>Active</option>
                <option value="inactive" @selected($customer->status->value === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-semibold">Available credit (₹)</label>
                <input name="available_credit" type="number" value="{{ old('available_credit', (int) $customer->available_credit) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold">Eligible offer (₹)</label>
                <input name="eligible_offer" type="number" value="{{ old('eligible_offer', (int) $customer->eligible_offer) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold">Minimum amount (₹)</label>
                <input name="credit_min" type="number" value="{{ old('credit_min', (int) $customer->credit_min) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold">Maximum amount (₹)</label>
                <input name="credit_max" type="number" value="{{ old('credit_max', (int) $customer->credit_max) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
            </div>
        </div>
        <button class="rounded-2xl brand-gradient px-5 py-3 text-sm font-bold text-white">Update customer</button>
    </form>
</x-layouts.admin>
