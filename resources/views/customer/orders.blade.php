<x-layouts.customer :title="'Orders · '.$appName">
    <div class="px-5 pt-6">
        <h1 class="text-2xl font-extrabold">Orders</h1>
        <p class="text-xs font-semibold text-brand">{{ $appName }}</p>
        <form method="GET" action="{{ route('orders') }}" class="mt-4">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="search" name="q" value="{{ $search }}" placeholder="Search transactions..." class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-brand">
        </form>

        <div class="mt-4 grid grid-cols-2 gap-2 rounded-2xl bg-white p-1">
            <a href="{{ route('orders', ['tab' => 'pending', 'q' => $search]) }}" class="rounded-xl py-2 text-center text-xs font-bold {{ $tab === 'pending' ? 'bg-brand text-white' : 'text-muted' }}">Pending</a>
            <a href="{{ route('orders', ['tab' => 'completed', 'q' => $search]) }}" class="rounded-xl py-2 text-center text-xs font-bold {{ $tab === 'completed' ? 'bg-brand text-white' : 'text-muted' }}">Completed</a>
        </div>
    </div>

    <div class="space-y-4 px-5 py-5">
        @forelse ($loans as $loan)
            @php $latest = $loan->payments->first(); @endphp
            <article class="rounded-[24px] border border-slate-100 bg-white p-4 shadow-sm" x-data="{ open: false }">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-extrabold">{{ $loan->title }}</h2>
                        <p class="text-xs text-muted">{{ $loan->reference() }}</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $loan->status->badgeClass() }}">{{ $loan->status->customerLabel() }}</span>
                </div>
                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <dt class="text-muted">Total amount</dt>
                        <dd class="font-bold">{{ \App\Support\Money::format($loan->amount) }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Due date</dt>
                        <dd class="font-bold">{{ optional($loan->due_date)->format('d M Y') ?? '—' }}</dd>
                    </div>
                </dl>

                @if ($latest?->status === \App\Enums\PaymentStatus::Rejected)
                    <p class="mt-3 rounded-xl bg-red-50 px-3 py-2 text-xs text-red-700">Rejected: {{ $latest->admin_notes }}</p>
                @endif
                @if ($latest?->status === \App\Enums\PaymentStatus::Pending)
                    <p class="mt-3 rounded-xl bg-brand-soft px-3 py-2 text-xs text-brand">Payment submitted and waiting for review.</p>
                @endif

                <div class="mt-4 flex gap-2">
                    @if ($loan->canAcceptPayment())
                        <a href="{{ route('loans.pay', $loan) }}" class="flex-1 rounded-2xl brand-gradient py-2.5 text-center text-sm font-bold text-white">Pay Now</a>
                    @endif
                    <button type="button" class="flex-1 rounded-2xl border border-slate-200 py-2.5 text-sm font-bold" @click="open = !open">View details</button>
                </div>
                <div class="mt-4 space-y-2 border-t border-slate-100 pt-4 text-sm" x-show="open" x-cloak>
                    <p><span class="text-muted">Loan amount:</span> <strong>{{ \App\Support\Money::format($loan->amount) }}</strong></p>
                    <p><span class="text-muted">Loan date:</span> <strong>{{ optional($loan->loan_date)->format('d M Y') ?? '—' }}</strong></p>
                    <p><span class="text-muted">Due date:</span> <strong>{{ optional($loan->due_date)->format('d M Y') ?? '—' }}</strong></p>
                    @if ($loan->description)
                        <p class="text-muted">{{ $loan->description }}</p>
                    @endif
                </div>
            </article>
        @empty
            <p class="rounded-2xl bg-white p-6 text-sm text-muted">No loans in this tab yet.</p>
        @endforelse
    </div>
</x-layouts.customer>
