<x-layouts.admin title="{{ $loan->title }}" heading="{{ $loan->title }}">
    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('admin.loans.edit', $loan) }}" class="rounded-2xl bg-white px-4 py-2 text-sm font-bold shadow-sm">Edit</a>
        @if ($loan->canBeDeleted())
            <form method="POST" action="{{ route('admin.loans.destroy', $loan) }}" onsubmit="return confirm('Delete this loan?')">
                @csrf
                @method('DELETE')
                <button class="rounded-2xl bg-red-50 px-4 py-2 text-sm font-bold text-red-600">Delete</button>
            </form>
        @endif
    </div>
    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded-3xl bg-white p-5 shadow-sm text-sm">
            <p><span class="text-muted">Customer:</span>
                @if ($loan->user)
                    <a class="font-bold text-brand" href="{{ route('admin.customers.show', $loan->user) }}">{{ $loan->user->name }}</a>
                @else
                    <strong>Featured offer</strong>
                @endif
            </p>
            <p class="mt-2"><span class="text-muted">Loan ID:</span> <strong>{{ $loan->reference() }}</strong></p>
            <p class="mt-2"><span class="text-muted">Amount:</span> <strong>{{ \App\Support\Money::format($loan->amount) }}</strong></p>
            <p class="mt-2"><span class="text-muted">Status:</span> <strong>{{ $loan->status->label() }}</strong></p>
            <p class="mt-2"><span class="text-muted">Dates:</span> {{ optional($loan->loan_date)->format('d M Y') }} → {{ optional($loan->due_date)->format('d M Y') }}</p>
            <p class="mt-2 text-muted">{{ $loan->description }}</p>
        </section>
        <section class="rounded-3xl bg-white p-5 shadow-sm">
            <h2 class="font-bold">Payment submissions</h2>
            <div class="mt-3 space-y-2 text-sm">
                @forelse ($loan->payments as $payment)
                    <a href="{{ route('admin.payments.show', $payment) }}" class="flex justify-between rounded-2xl bg-slate-50 px-4 py-3">
                        <span>{{ $payment->transaction_id }}</span>
                        <span>{{ $payment->status->label() }}</span>
                    </a>
                @empty
                    <p class="text-muted">No submissions yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.admin>
