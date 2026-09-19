<x-layouts.admin title="Payment review" heading="Payment review">
    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded-3xl bg-white p-5 shadow-sm text-sm space-y-2">
            <p><span class="text-muted">Customer:</span> <strong>{{ $payment->user->name }}</strong></p>
            <p><span class="text-muted">Phone:</span> <strong>{{ $payment->user->displayPhone() }}</strong></p>
            <p><span class="text-muted">Loan ID:</span> <strong>{{ $payment->loan->reference() }}</strong></p>
            <p><span class="text-muted">Loan title:</span> <strong>{{ $payment->loan->title }}</strong></p>
            <p><span class="text-muted">Loan amount:</span> <strong>{{ \App\Support\Money::format($payment->loan->amount) }}</strong></p>
            <p><span class="text-muted">Transaction ID:</span> <strong>{{ $payment->transaction_id }}</strong></p>
            <p><span class="text-muted">Method:</span> <strong>{{ $payment->paymentMethod?->name ?? '—' }}</strong></p>
            <p><span class="text-muted">Submitted:</span> <strong>{{ optional($payment->submitted_at)->format('d M Y H:i') }}</strong></p>
            <p><span class="text-muted">Status:</span> <strong>{{ $payment->status->label() }}</strong></p>
            @if ($payment->admin_notes)
                <p><span class="text-muted">Admin notes:</span> {{ $payment->admin_notes }}</p>
            @endif
        </section>
        <section class="rounded-3xl bg-white p-5 shadow-sm">
            <h2 class="font-bold">Payment screenshot</h2>
            <a href="{{ route('admin.payments.screenshot', $payment) }}" target="_blank" class="mt-3 block overflow-hidden rounded-2xl bg-slate-50">
                <img src="{{ route('admin.payments.screenshot', $payment) }}" alt="Payment screenshot" class="max-h-80 w-full object-contain">
            </a>
        </section>
    </div>

    @if ($payment->isPending())
        <form method="POST" class="mt-4 space-y-3 rounded-3xl bg-white p-5 shadow-sm">
            @csrf
            <label class="block text-sm font-semibold">Admin notes / rejection reason</label>
            <textarea name="admin_notes" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('admin_notes') }}</textarea>
            @error('admin_notes') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            <div class="flex gap-3">
                <button formmethod="POST" formaction="{{ route('admin.payments.approve', $payment) }}" class="rounded-2xl brand-gradient px-4 py-3 text-sm font-bold text-white">Approve payment</button>
                <button formmethod="POST" formaction="{{ route('admin.payments.reject', $payment) }}" class="rounded-2xl bg-red-50 px-4 py-3 text-sm font-bold text-red-600">Reject payment</button>
            </div>
        </form>
    @endif

    <section class="mt-4 rounded-3xl bg-white p-5 shadow-sm">
        <h2 class="font-bold">Payment history for this loan</h2>
        <div class="mt-3 space-y-2 text-sm">
            @forelse ($history as $item)
                <a href="{{ route('admin.payments.show', $item) }}" class="flex justify-between rounded-2xl bg-slate-50 px-4 py-3">
                    <span>{{ $item->transaction_id }} · {{ optional($item->submitted_at)->format('d M Y') }}</span>
                    <span>{{ $item->status->label() }}</span>
                </a>
            @empty
                <p class="text-muted">No earlier submissions.</p>
            @endforelse
        </div>
    </section>
</x-layouts.admin>
