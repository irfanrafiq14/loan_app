<x-layouts.admin title="{{ $customer->name }}" heading="{{ $customer->name }}">
    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('admin.customers.edit', $customer) }}" class="rounded-2xl bg-white px-4 py-2 text-sm font-bold shadow-sm">Edit</a>
        <a href="{{ route('admin.login-links.create', ['customer' => $customer->id]) }}" class="rounded-2xl brand-gradient px-4 py-2 text-sm font-bold text-white">Create login link</a>
        <a href="{{ route('admin.loans.create', ['customer' => $customer->id]) }}" class="rounded-2xl bg-white px-4 py-2 text-sm font-bold shadow-sm">Create loan</a>
        @if ($customer->isActive())
            <form method="POST" action="{{ route('admin.customers.deactivate', $customer) }}">
                @csrf
                <button class="rounded-2xl bg-red-50 px-4 py-2 text-sm font-bold text-red-600">Deactivate</button>
            </form>
        @endif
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <section class="rounded-3xl bg-white p-5 shadow-sm">
            <p class="text-sm text-muted">Phone</p>
            <p class="font-bold">{{ $customer->displayPhone() }}</p>
            <p class="mt-3 text-sm text-muted">Status</p>
            <p class="font-bold capitalize">{{ $customer->status->value }}</p>
        </section>
        <section class="lg:col-span-2 rounded-3xl bg-white p-5 shadow-sm">
            <h2 class="font-bold">Loans</h2>
            <div class="mt-3 space-y-2 text-sm">
                @forelse ($customer->loans as $loan)
                    <a href="{{ route('admin.loans.show', $loan) }}" class="flex justify-between rounded-2xl bg-slate-50 px-4 py-3">
                        <span>{{ $loan->title }}</span>
                        <span>{{ \App\Support\Money::format($loan->amount) }} · {{ $loan->status->label() }}</span>
                    </a>
                @empty
                    <p class="text-muted">No loans yet.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="mt-4 rounded-3xl bg-white p-5 shadow-sm">
        <h2 class="font-bold">Payments</h2>
        <div class="mt-3 space-y-2 text-sm">
            @forelse ($customer->payments as $payment)
                <a href="{{ route('admin.payments.show', $payment) }}" class="flex justify-between rounded-2xl bg-slate-50 px-4 py-3">
                    <span>{{ $payment->loan->title }} · {{ $payment->transaction_id }}</span>
                    <span>{{ $payment->status->label() }}</span>
                </a>
            @empty
                <p class="text-muted">No payments yet.</p>
            @endforelse
        </div>
    </section>
</x-layouts.admin>
