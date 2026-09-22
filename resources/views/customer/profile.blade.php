@php
    $limit = $customer->creditLimit();
    $available = $customer->creditAvailable();
    $percent = $customer->creditUtilizationPercent();
    $radius = 36;
    $circumference = 2 * 3.1416 * $radius;
    $dash = $circumference * min(100, max(0, $percent)) / 100;
@endphp

<x-layouts.customer :title="'Profile · '.$appName">
    <div class="px-5 pt-6 lg:grid lg:grid-cols-[1fr_22rem] lg:items-start lg:gap-6 lg:px-0 lg:pt-0">
        <div>
            <section class="rounded-[28px] bg-white p-5 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Hello</p>
                <div class="mt-4 flex items-center gap-4">
                    <div class="relative">
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-3xl font-extrabold text-brand">
                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                        </div>
                        <span class="absolute bottom-0 right-0 flex h-6 w-6 items-center justify-center rounded-full bg-brand text-white">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="m5 12 5 5L20 7"/></svg>
                        </span>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">User profile</p>
                        <h1 class="mt-1 text-2xl font-extrabold">{{ $customer->name }}</h1>
                        <p class="mt-1 text-sm text-muted">{{ $customer->maskedPhone() }}</p>
                    </div>
                </div>
            </section>

            <section class="mt-4 rounded-[28px] bg-white p-5 shadow-sm lg:hidden">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Credit limit</p>
                <div class="mt-4 flex items-center gap-4">
                    <svg class="credit-ring" viewBox="0 0 100 100" aria-hidden="true">
                        <circle cx="50" cy="50" r="{{ $radius }}" fill="none" stroke="#E5E7EB" stroke-width="10"/>
                        <circle cx="50" cy="50" r="{{ $radius }}" fill="none" stroke="#0EA5E9" stroke-width="10" stroke-linecap="round"
                                stroke-dasharray="{{ $dash }} {{ $circumference }}" transform="rotate(-90 50 50)"/>
                        <text x="50" y="48" text-anchor="middle" font-size="16" font-weight="800" fill="#111827">{{ $percent }}%</text>
                        <text x="50" y="62" text-anchor="middle" font-size="7" font-weight="700" fill="#9CA3AF">UTILIZED</text>
                    </svg>
                    <div>
                        <p class="text-3xl font-extrabold">{{ \App\Support\Money::format($limit) }}</p>
                        <p class="mt-1 text-sm text-muted">{{ \App\Support\Money::format($available) }} available · {{ $percent }}% utilized</p>
                    </div>
                </div>
            </section>

            <x-support-link variant="card" :url="$supportGmailUrl">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-brand">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21a9 9 0 1 0-9-9 9 9 0 0 0 9 9Zm0-12V8m0 8h.01"/></svg>
                </div>
                <div>
                    <p class="font-extrabold">Support</p>
                    <p class="text-sm text-muted">Contact the support team</p>
                </div>
            </x-support-link>

            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button class="flex w-full items-center justify-center gap-2 rounded-[22px] bg-rose-50 py-4 text-sm font-bold text-rose-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H6"/></svg>
                    Log out
                </button>
            </form>
        </div>

        <section class="mt-4 hidden rounded-[28px] bg-white p-6 text-center shadow-sm lg:mt-0 lg:block">
            <p class="text-sm font-bold text-slate-500">Credit limit</p>
            <svg class="mx-auto mt-4 h-44 w-44" viewBox="0 0 100 100" aria-hidden="true">
                <circle cx="50" cy="50" r="{{ $radius }}" fill="none" stroke="#E5E7EB" stroke-width="8"/>
                <circle cx="50" cy="50" r="{{ $radius }}" fill="none" stroke="#0EA5E9" stroke-width="8" stroke-linecap="round"
                        stroke-dasharray="{{ $dash }} {{ $circumference }}" transform="rotate(-90 50 50)"/>
                <text x="50" y="48" text-anchor="middle" font-size="14" font-weight="800" fill="#111827">{{ $percent }}%</text>
                <text x="50" y="60" text-anchor="middle" font-size="6" font-weight="700" fill="#9CA3AF">UTILIZED</text>
            </svg>
            <div class="mt-4 rounded-2xl bg-brand-soft px-4 py-3 text-left">
                <p class="text-xs text-muted">Total limit</p>
                <p class="font-extrabold">{{ \App\Support\Money::format($limit) }}</p>
                <p class="mt-2 text-xs text-muted">Available</p>
                <p class="font-extrabold">{{ \App\Support\Money::format($available) }}</p>
            </div>
            <p class="mt-4 text-xs leading-5 text-muted">Maintain a healthy credit score by keeping your utilization below 30%. Your limit is reviewed every 6 months.</p>
            <button type="button" class="mt-5 w-full rounded-full border border-slate-300 py-3 text-sm font-bold" @click="requestIncrease()">Request increase</button>
        </section>
    </div>
</x-layouts.customer>
