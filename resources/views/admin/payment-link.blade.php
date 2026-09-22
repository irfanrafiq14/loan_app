<x-layouts.admin title="Payment link" heading="Payment link">
    <p class="mb-4 max-w-xl text-sm text-muted">Customers copy this UPI ID or URL on the payment page. Payment method logos on the client app are fixed.</p>

    <form method="POST" action="{{ route('admin.payment-link.update') }}" class="max-w-xl space-y-4 rounded-3xl bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <div>
            <label class="mb-2 block text-sm font-semibold">Payment link</label>
            <input name="payment_link" value="{{ old('payment_link', $paymentLink) }}" placeholder="778028656@omni" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
            @error('payment_link') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <button class="rounded-2xl brand-gradient px-5 py-3 text-sm font-bold text-white">Save payment link</button>
    </form>
</x-layouts.admin>
