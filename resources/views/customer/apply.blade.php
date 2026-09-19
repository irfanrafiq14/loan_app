<x-layouts.customer :title="'Apply · '.$appName">
    <div class="px-5 pt-6">
        <a href="{{ route('home') }}" class="text-sm font-semibold text-brand">← Back to home</a>
        <h1 class="mt-3 text-2xl font-extrabold">Apply for {{ $loan->title }}</h1>
        <p class="text-sm text-muted">{{ \App\Support\Money::format($loan->amount) }}</p>
    </div>

    <div class="space-y-4 px-5 py-5">
        <section class="rounded-[24px] border border-slate-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-brand">Step 1 · Offer details</p>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-muted">Loan title</dt><dd class="font-bold">{{ $loan->title }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted">Amount</dt><dd class="font-bold">{{ \App\Support\Money::format($loan->amount) }}</dd></div>
                @if ($loan->due_date)
                    <div class="flex justify-between"><dt class="text-muted">Due date</dt><dd class="font-bold">{{ $loan->due_date->format('d M Y') }}</dd></div>
                @endif
            </dl>
            @if ($loan->description)
                <p class="mt-3 text-sm text-muted">{{ $loan->description }}</p>
            @endif
        </section>

        <section class="rounded-[24px] border border-slate-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-brand">Step 2 · Dues check</p>
            @if ($hasOutstandingDues)
                <div class="mt-3 rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-bold">Before you apply, clear all dues.</p>
                    <p class="mt-1">You have unpaid loans. Pay them first, then apply for a new offer.</p>
                </div>
                <div class="mt-3 space-y-2">
                    @foreach ($dues as $due)
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                            <div>
                                <p class="font-bold">{{ $due->title }}</p>
                                <p class="text-muted">{{ \App\Support\Money::format($due->amount) }}</p>
                            </div>
                            @if ($due->canAcceptPayment())
                                <a href="{{ route('loans.pay', $due) }}" class="rounded-xl brand-gradient px-3 py-2 text-xs font-bold text-white">Pay Now</a>
                            @else
                                <a href="{{ route('orders', ['tab' => 'pending']) }}" class="text-xs font-bold text-brand">View</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-3 rounded-2xl bg-brand-soft px-4 py-3 text-sm font-semibold text-brand">All dues are clear. You can continue this application.</p>
            @endif
        </section>

        <section class="rounded-[24px] border border-slate-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-brand">Step 3 · Process application</p>
            @if ($hasOutstandingDues)
                <p class="mt-3 text-sm text-muted">Application is locked until every pending loan is cleared.</p>
            @else
                <p class="mt-3 text-sm text-muted">Confirm to submit this offer. It will appear in your Orders page as a pending loan.</p>
                <form method="POST" action="{{ route('loans.apply.store', $loan) }}" class="mt-4">
                    @csrf
                    <button class="w-full rounded-2xl brand-gradient py-3.5 text-sm font-bold text-white">Confirm and process</button>
                </form>
            @endif
        </section>
    </div>
</x-layouts.customer>
