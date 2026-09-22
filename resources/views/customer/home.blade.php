<x-layouts.customer :title="'Home · '.$appName">
    <header class="px-5 pt-6 lg:px-0 lg:pt-0">
        <p class="text-xl font-extrabold lg:text-2xl">{{ $customer->greeting() }}</p>
    </header>

    <section class="px-5 pt-5 lg:grid lg:grid-cols-2 lg:gap-6 lg:px-0">
        @php
            $gaugePercent = max(0, min(100, (float) $barPercent));
            $gaugeCx = 120;
            $gaugeCy = 118;
            $gaugeR = 92;
            $gaugeTheta = M_PI * (1 - $gaugePercent / 100);
            $gaugeKnobX = $gaugeCx + ($gaugeR * cos($gaugeTheta));
            $gaugeKnobY = $gaugeCy - ($gaugeR * sin($gaugeTheta));
            $gaugeStartX = $gaugeCx - $gaugeR;
            $gaugeEndX = $gaugeCx + $gaugeR;
            $gaugePath = 'M '.$gaugeStartX.' '.$gaugeCy.' A '.$gaugeR.' '.$gaugeR.' 0 0 1 '.$gaugeEndX.' '.$gaugeCy;
        @endphp
        <div class="amount-gauge hero-gradient rounded-[28px] px-5 pb-5 pt-6 text-white shadow-lg">
            <div class="amount-gauge-arc">
                <svg viewBox="0 0 240 140" class="amount-gauge-svg" aria-hidden="true">
                    <path d="{{ $gaugePath }}" fill="none" stroke="rgba(255,255,255,0.28)" stroke-width="14" stroke-linecap="round"/>
                    @if ($gaugePercent > 0.4)
                        <path d="{{ $gaugePath }}" fill="none" stroke="#ffffff" stroke-width="14" stroke-linecap="round"
                              pathLength="100" stroke-dasharray="{{ number_format($gaugePercent, 2, '.', '') }} 100"/>
                    @endif
                    <circle cx="{{ number_format($gaugeKnobX, 2, '.', '') }}" cy="{{ number_format($gaugeKnobY, 2, '.', '') }}" r="8.5" fill="#0F172A"/>
                    <circle cx="{{ number_format($gaugeKnobX, 2, '.', '') }}" cy="{{ number_format($gaugeKnobY, 2, '.', '') }}" r="3.2" fill="#ffffff"/>
                </svg>
                <div class="amount-gauge-value">
                    <p class="text-[2rem] font-extrabold leading-none tracking-tight lg:text-4xl">{{ \App\Support\Money::format($barAmount) }}</p>
                    <p class="mt-1.5 text-sm font-medium text-white/80">Selected amount</p>
                </div>
            </div>
            <div class="mt-1 flex items-end justify-between px-1 text-sm">
                <div>
                    <p class="font-extrabold">{{ \App\Support\Money::format($barMin) }}</p>
                    <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.16em] text-white/70">Minimum</p>
                </div>
                <div class="text-right">
                    <p class="font-extrabold">{{ \App\Support\Money::format($barMax) }}</p>
                    <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.16em] text-white/70">Maximum</p>
                </div>
            </div>
        </div>

        <div class="mt-5 rounded-[24px] bg-white p-5 shadow-sm lg:mt-0">
            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-brand">Personalized offer</p>
            <h2 class="mt-2 text-xl font-extrabold">You are eligible for {{ \App\Support\Money::format($customer->eligible_offer) }}</h2>
            <p class="mt-2 text-sm text-muted">Your trust score and repayment history qualify you for selected rates. Review limits below and pick a loan that fits your plan. Based on your {{ $appName }} profile.</p>
            @if ($featuredLoans->isNotEmpty())
                <button type="button" class="btn-primary mt-5 max-w-xs" @click="applyLoan()">Apply now</button>
            @endif
        </div>
    </section>

    <section class="px-5 py-6 lg:px-0">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-extrabold">Featured loans</h2>
            <p class="text-sm font-bold text-brand">Total {{ \App\Support\Money::format($featuredTotal) }}</p>
        </div>
        <div class="space-y-3">
            @forelse ($featuredLoans as $loan)
                <article class="flex items-center gap-3 rounded-[22px] bg-white px-4 py-3 shadow-sm">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-brand-soft">
                        <img src="{{ $loan->featuredImageUrl() }}" alt="{{ $loan->title }}" class="h-12 w-12 rounded-2xl object-cover" width="48" height="48">
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate font-bold">{{ $loan->title }}</h3>
                        <p class="text-sm font-semibold text-muted">{{ \App\Support\Money::format($loan->amount) }}</p>
                    </div>
                    <button type="button" class="shrink-0 rounded-full bg-brand px-4 py-2 text-sm font-bold text-white" @click="applyLoan()">Apply</button>
                </article>
            @empty
                <p class="rounded-2xl bg-white p-5 text-sm text-muted">No featured loans yet. Your administrator will add offers here.</p>
            @endforelse
        </div>

        <div class="trust-card mt-5 rounded-[24px] p-5 shadow-sm">
            <h3 class="text-lg font-extrabold">Your credit, secured</h3>
            <p class="mt-2 text-sm text-white/70">Bank-grade encryption protects your data in transit and at rest. We follow strict privacy practices so your financial information stays yours.</p>
            <div class="mt-5 flex flex-wrap gap-6 text-[11px] font-extrabold uppercase tracking-[0.18em] text-white/80">
                <span>RBI norms</span>
                <span>SSL secured</span>
            </div>
        </div>
    </section>
</x-layouts.customer>
