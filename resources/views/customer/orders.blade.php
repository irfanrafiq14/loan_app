<x-layouts.customer :title="'Orders · '.$appName">
    <div class="px-5 pt-6 lg:px-0 lg:pt-0">
        <form method="GET" action="{{ route('orders') }}">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="flex items-center gap-3 rounded-full bg-white px-4 py-3 shadow-sm">
                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
                <input type="search" name="q" value="{{ $search }}" placeholder="Search transactions..." class="w-full bg-transparent text-sm outline-none">
            </div>
        </form>

        <div class="mt-6 flex gap-8 border-b border-slate-200 px-1 pb-3">
            <a href="{{ route('orders', ['tab' => 'pending', 'q' => $search]) }}" class="tab-underline text-sm font-bold {{ $tab === 'pending' ? 'is-active' : '' }}">Pending</a>
            <a href="{{ route('orders', ['tab' => 'completed', 'q' => $search]) }}" class="tab-underline text-sm font-bold {{ $tab === 'completed' ? 'is-active' : '' }}">Completed</a>
        </div>
        <p class="mt-5 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $tab === 'completed' ? 'Past loans' : 'Active obligations' }}</p>
    </div>

    <div class="space-y-4 px-5 py-4 lg:px-0">
        @forelse ($loans as $loan)
            @php $latest = $loan->payments->first(); @endphp
            <article class="rounded-[28px] bg-white p-5 shadow-sm" x-data="{ open: false }">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-brand-soft text-brand">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M8 8V7a4 4 0 1 1 8 0v1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M6.5 8h11l.8 11.2A2 2 0 0 1 16.3 21H7.7a2 2 0 0 1-2-1.8L6.5 8Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M12 12v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg font-extrabold leading-tight">{{ $loan->title }}</h2>
                        <p class="mt-0.5 text-xs text-muted">Reference: {{ $loan->reference() }}</p>
                    </div>
                    <div class="text-right">
                        @if ($tab === 'completed')
                            <span class="rounded-full bg-brand-soft px-3 py-1 text-xs font-bold text-brand">Paid</span>
                        @else
                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Total due</p>
                            <p class="text-xl font-extrabold">{{ \App\Support\Money::format($loan->totalDueAmount()) }}</p>
                        @endif
                    </div>
                </div>

                @if ($latest?->status === \App\Enums\PaymentStatus::Rejected)
                    <p class="mt-3 rounded-xl bg-red-50 px-3 py-2 text-xs text-red-700">Rejected: {{ $latest->admin_notes }}</p>
                @endif
                @if ($latest?->status === \App\Enums\PaymentStatus::Pending)
                    <p class="mt-3 rounded-xl bg-brand-soft px-3 py-2 text-xs text-brand">Payment submitted and waiting for review.</p>
                @endif

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                    <p class="inline-flex items-center gap-2 text-sm font-semibold {{ $tab === 'completed' ? 'text-muted' : 'text-red-500' }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 3v2m8-2v2M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/></svg>
                        Due {{ optional($loan->due_date)->format('M j, Y') ?? '—' }}
                    </p>
                    <div class="flex items-center gap-4">
                        <button type="button" class="inline-flex items-center gap-1 text-sm font-bold text-slate-700" @click="open = !open">
                            View Details
                            <svg class="h-4 w-4 transition" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        @if ($loan->canAcceptPayment())
                            <a href="{{ route('loans.pay', $loan) }}" class="btn-primary w-auto min-w-28 px-6 py-2.5 text-sm shadow-[0_8px_20px_rgba(2,132,199,0.28)]">Pay Now</a>
                        @endif
                    </div>
                </div>

                <div class="mt-4 overflow-hidden" x-show="open" x-cloak x-transition>
                    <div class="grid grid-cols-2 gap-4 rounded-2xl bg-brand-soft px-5 py-4">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Loan amount</p>
                            <p class="mt-1 text-lg font-extrabold">{{ \App\Support\Money::format($loan->amount) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Loan date</p>
                            <p class="mt-1 text-lg font-extrabold">{{ optional($loan->loan_date)->format('M j, Y') ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <p class="rounded-2xl bg-white p-6 text-sm text-muted">No loans in this tab yet.</p>
        @endforelse

        @if ($tab === 'pending')
            <div class="rounded-[22px] bg-gradient-to-r from-sky-400 to-sky-600 px-4 py-4 text-sm font-semibold text-white shadow-sm">
                On time payment can increase your credit limit and unlock premium loan rates.
            </div>
        @endif
    </div>
</x-layouts.customer>
