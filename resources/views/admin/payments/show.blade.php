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
            @if (filled($payment->screenshot_path))
                <a href="{{ route('admin.payments.screenshot', $payment) }}" target="_blank" class="mt-3 block overflow-hidden rounded-2xl bg-slate-50">
                    <img src="{{ route('admin.payments.screenshot', $payment) }}" alt="Payment screenshot" class="max-h-80 w-full object-contain">
                </a>
            @else
                <p class="mt-3 text-sm text-muted">No screenshot uploaded.</p>
            @endif
            <form method="POST" action="{{ route('admin.payments.screenshot.update', $payment) }}" enctype="multipart/form-data" class="mt-4 space-y-3 border-t border-slate-100 pt-4">
                @csrf
                @method('PUT')
                <label class="block text-sm font-semibold" for="screenshot">Replace screenshot</label>
                <input id="screenshot" type="file" name="screenshot" accept="image/jpeg,image/png,image/webp" class="block w-full cursor-pointer rounded-xl border border-slate-200 bg-slate-50 p-2 text-sm file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-slate-900 file:px-4 file:py-2.5 file:font-bold file:text-white">
                @error('screenshot') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                @if (filled($payment->screenshot_path))
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remove_screenshot" value="1" class="rounded border-slate-300">
                        Remove current screenshot
                    </label>
                @endif
                <button class="flex w-full items-center justify-center gap-2 rounded-2xl brand-gradient px-5 py-3.5 text-base font-extrabold text-white shadow-md transition hover:brightness-105 focus:outline-none focus:ring-4 focus:ring-sky-200">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16.5V19a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-2.5M12 15V4m0 0L8 8m4-4 4 4" /></svg>
                    Update screenshot
                </button>
            </form>
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
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-bold">Payment history for this loan</h2>
            <form method="POST" action="{{ route('admin.payments.destroy', $payment) }}" onsubmit="return confirm('Delete this payment history and its screenshot?')">
                @csrf
                @method('DELETE')
                <button class="text-sm font-bold text-red-600">Delete current record</button>
            </form>
        </div>
        <div class="mt-3 space-y-2 text-sm">
            @forelse ($history as $item)
                <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 px-4 py-3">
                    <a href="{{ route('admin.payments.show', $item) }}" class="flex min-w-0 flex-1 justify-between gap-3">
                        <span class="truncate">{{ $item->transaction_id }} · {{ optional($item->submitted_at)->format('d M Y') }}</span>
                        <span class="shrink-0">{{ $item->status->label() }}</span>
                    </a>
                    <form method="POST" action="{{ route('admin.payments.destroy', $item) }}" onsubmit="return confirm('Delete this payment history and its screenshot?')">
                        @csrf
                        @method('DELETE')
                        <button class="shrink-0 text-xs font-bold text-red-600">Delete</button>
                    </form>
                </div>
            @empty
                <p class="text-muted">No earlier submissions.</p>
            @endforelse
        </div>
    </section>
</x-layouts.admin>
