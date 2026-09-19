<x-layouts.customer :title="'Home · '.$appName">
    <header class="flex items-center justify-between px-5 pt-6">
        <div>
            <p class="text-lg font-extrabold">{{ $customer->greeting() }}</p>
            <p class="text-xs font-semibold text-brand">{{ $appName }}</p>
        </div>
        <a href="{{ route('profile') }}" class="flex h-11 w-11 items-center justify-center rounded-full bg-white shadow-sm">
            <svg class="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm8 8a8 8 0 1 0-16 0"/></svg>
        </a>
    </header>

    <section class="px-5 pt-5">
        <div class="brand-gradient rounded-[28px] p-5 text-white shadow-lg">
            <p class="text-sm text-white/80">{{ $barLabel }}</p>
            <p class="mt-1 text-3xl font-extrabold">{{ \App\Support\Money::format($barAmount) }}</p>
            <div class="mt-6 rounded-2xl bg-white/10 p-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-white/80">Current amount</span>
                    <span class="font-bold">{{ \App\Support\Money::format($barAmount) }}</span>
                </div>
                <div class="relative mt-4 h-2 rounded-full bg-white/35">
                    <div class="absolute inset-y-0 left-0 rounded-full bg-white" style="width: {{ number_format((float) $barPercent, 2, '.', '') }}%"></div>
                    <div class="absolute top-1/2 h-5 w-5 -translate-x-1/2 -translate-y-1/2 rounded-full bg-white shadow" style="left: {{ number_format((float) $barPercent, 2, '.', '') }}%"></div>
                </div>
                <div class="mt-3 flex justify-between text-xs text-white/80">
                    <span>Min {{ \App\Support\Money::format($barMin) }}</span>
                    <span>Max {{ \App\Support\Money::format($barMax) }}</span>
                </div>
            </div>
        </div>
    </section>

    <section class="px-5 pt-5">
        <div class="rounded-[24px] border border-slate-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-brand">Personalized offer</p>
            <h2 class="mt-2 text-xl font-extrabold">You are eligible for {{ \App\Support\Money::format($customer->eligible_offer) }}</h2>
            <p class="mt-2 text-sm text-muted">Based on your {{ $appName }} profile, this limit is reserved for you. Apply now to review your current loan offers.</p>
            @if ($featuredLoans->isNotEmpty())
                <button type="button" class="mt-4 inline-flex rounded-2xl brand-gradient px-5 py-3 text-sm font-bold text-white" @click="applyLoan()">Apply Now</button>
            @endif
        </div>
    </section>

    <section class="px-5 py-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-extrabold">Featured loans</h2>
            <a href="{{ route('orders') }}" class="text-sm font-semibold text-brand">See all</a>
        </div>
        <div class="space-y-3">
            @forelse ($featuredLoans as $loan)
                <article class="flex items-center gap-3 rounded-[24px] border border-slate-100 bg-white px-4 py-3 shadow-sm">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-soft">
                        <img src="{{ $loan->featuredImageUrl() }}" alt="{{ $loan->title }}" class="h-12 w-12 rounded-full object-cover" width="48" height="48" style="width:48px;height:48px;object-fit:cover">
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate font-bold">{{ $loan->title }}</h3>
                        <p class="text-sm text-muted">{{ \App\Support\Money::format($loan->amount) }}</p>
                    </div>
                    <button type="button" class="shrink-0 rounded-full bg-brand px-4 py-2 text-sm font-bold text-white" @click="applyLoan()">Apply</button>
                </article>
            @empty
                <p class="rounded-2xl bg-white p-5 text-sm text-muted">No featured loans yet. Your administrator will add offers here.</p>
            @endforelse
        </div>
    </section>
</x-layouts.customer>
