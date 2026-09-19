<x-layouts.admin title="Dashboard" heading="Dashboard">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            ['Total customers', $stats['total_customers']],
            ['Active loans', $stats['active_loans']],
            ['Pending loan payments', $stats['pending_payments']],
            ['Completed loan payments', $stats['completed_payments']],
            ['Total loan amount', \App\Support\Money::format($stats['total_loan_amount'])],
            ['Total amount paid', \App\Support\Money::format($stats['total_amount_paid'])],
        ] as $card)
            <article class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-sm text-muted">{{ $card[0] }}</p>
                <p class="mt-2 text-2xl font-extrabold text-brand">{{ $card[1] }}</p>
            </article>
        @endforeach
    </div>
</x-layouts.admin>
