@php $appUrl = \App\Support\AppBrand::brandedLocalUrl($customer); @endphp
<x-layouts.admin title="{{ $customer->name }}" heading="{{ $customer->name }}">
    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('admin.customers.edit', $customer) }}" class="rounded-2xl bg-white px-4 py-2 text-sm font-bold shadow-sm">Edit</a>
        <a href="{{ route('admin.loans.create', ['customer' => $customer->id]) }}" class="rounded-2xl bg-white px-4 py-2 text-sm font-bold shadow-sm">Create loan</a>
        @if ($customer->isActive())
            <form method="POST" action="{{ route('admin.customers.deactivate', $customer) }}">
                @csrf
                <button class="rounded-2xl bg-red-50 px-4 py-2 text-sm font-bold text-red-600">Deactivate</button>
            </form>
        @endif
    </div>

    <section class="mb-4 rounded-3xl bg-white p-5 shadow-sm" x-data="{ copied: false }">
        <p class="text-sm text-muted">App name</p>
        <p class="text-lg font-extrabold">{{ $customer->brandedName() ?: '—' }}</p>
        <p class="mt-4 text-sm text-muted">Send this app link</p>
        <p class="mt-1 text-sm">Copy this URL. The long token shows the app name on first visit. The customer then signs in with phone and OTP.</p>
        <div class="mt-3 flex flex-wrap items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3">
            <p class="min-w-0 flex-1 break-all font-bold" x-ref="appLink">{{ $appUrl }}</p>
            <button type="button" class="rounded-full bg-slate-900 px-4 py-2 text-xs font-bold text-white"
                    @click="navigator.clipboard.writeText($refs.appLink.textContent.trim()); copied = true; setTimeout(() => copied = false, 2000)"
                    x-text="copied ? 'Copied' : 'Copy link'"></button>
        </div>
    </section>

    <div class="grid gap-4 lg:grid-cols-3">
        <section class="rounded-3xl bg-white p-5 shadow-sm">
            <p class="text-sm text-muted">Phone</p>
            <p class="font-bold">{{ $customer->displayPhone() }}</p>
            <p class="mt-3 text-sm text-muted">Status</p>
            <p class="font-bold capitalize">{{ $customer->status->value }}</p>
            <p class="mt-3 text-sm text-muted">Payment link</p>
            <p class="break-all font-bold">{{ $customer->paymentLink() !== '' ? $customer->paymentLink() : '—' }}</p>
        </section>
        <section class="lg:col-span-2 rounded-3xl bg-white p-5 shadow-sm">
            <h2 class="font-bold">Loans</h2>
            <div class="mt-3 space-y-2 text-sm">
                @forelse ($customer->loans as $loan)
                    <a href="{{ route('admin.loans.show', $loan) }}" class="flex justify-between rounded-2xl bg-slate-50 px-4 py-3">
                        <span>{{ $loan->title }}</span>
                        <span>{{ \App\Support\Money::format($loan->totalDueAmount()) }} · {{ $loan->status->label() }}</span>
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
