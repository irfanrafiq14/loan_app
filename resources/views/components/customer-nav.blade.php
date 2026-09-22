@props(['variant' => 'bottom'])

@php
    $items = [
        ['route' => 'home', 'label' => 'Home', 'match' => 'home', 'icon' => 'home'],
        ['route' => 'orders', 'label' => 'Orders', 'match' => 'orders', 'icon' => 'orders'],
        ['route' => 'profile', 'label' => 'Profile', 'match' => 'profile', 'icon' => 'profile'],
    ];
@endphp

@if ($variant === 'sidebar')
    <aside class="customer-sidebar hidden lg:flex lg:flex-col">
        <div class="mb-8 flex items-center gap-3 px-3">
            <span class="wallet-mark h-11 w-11 rounded-xl">
                <x-wallet-mark class="h-6 w-6" />
            </span>
            <div>
                <p class="text-base font-extrabold leading-tight">{{ $appName }}</p>
                <p class="text-xs text-white/70">Credit & loans</p>
            </div>
        </div>
        <nav class="flex flex-1 flex-col gap-1">
            @foreach ($items as $item)
                @php
                    $active = request()->routeIs($item['match']) || ($item['match'] === 'orders' && request()->routeIs('loans.pay'));
                @endphp
                <a href="{{ route($item['route']) }}" class="customer-sidebar-link {{ $active ? 'is-active' : '' }}">
                    @if ($item['icon'] === 'home')
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10.5 12 4l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg>
                    @elseif ($item['icon'] === 'orders')
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 8H7m14 0-1.2-3.2A2 2 0 0 0 17.9 3.5H8.1A2 2 0 0 0 6.2 4.8L5 8m16 0v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8"/><path stroke-linecap="round" stroke-width="1.8" d="M9 12h6"/></svg>
                    @else
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm8 8a8 8 0 1 0-16 0"/></svg>
                    @endif
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        <p class="px-3 pt-6 text-xs text-white/50">© {{ now()->year }} {{ $appName }}</p>
    </aside>
@else
    <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 backdrop-blur lg:hidden">
        <div class="mx-auto grid max-w-md grid-cols-3">
            @foreach ($items as $item)
                @php
                    $active = request()->routeIs($item['match']) || ($item['match'] === 'orders' && request()->routeIs('loans.pay'));
                @endphp
                <a href="{{ route($item['route']) }}" class="flex flex-col items-center gap-1 py-3 text-xs font-semibold {{ $active ? 'text-brand' : 'text-slate-400' }}">
                    @if ($item['icon'] === 'home')
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10.5 12 4l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg>
                    @elseif ($item['icon'] === 'orders')
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 8H7m14 0-1.2-3.2A2 2 0 0 0 17.9 3.5H8.1A2 2 0 0 0 6.2 4.8L5 8m16 0v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8"/></svg>
                    @else
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm8 8a8 8 0 1 0-16 0"/></svg>
                    @endif
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </nav>
@endif
