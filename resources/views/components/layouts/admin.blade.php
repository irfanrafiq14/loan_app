@props(['title' => 'Admin', 'heading' => 'Dashboard'])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }} · MaxWallet</title>
    <x-favicon name="MaxWallet" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-page font-sans antialiased text-ink" x-data="{ open: false }">
    <div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">
        <aside class="brand-gradient hidden text-white lg:flex lg:flex-col">
            <div class="flex items-center gap-3 px-6 py-6">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8.5A2.5 2.5 0 0 1 5.5 6H20v12H5.5A2.5 2.5 0 0 1 3 15.5v-7Zm14 3.5h.01"/></svg>
                </div>
                <div>
                    <p class="text-lg font-extrabold">MaxWallet</p>
                    <p class="text-xs text-white/70">Admin panel</p>
                </div>
            </div>
            <nav class="flex flex-1 flex-col gap-1 px-3">
                @foreach ([
                    ['admin.dashboard', 'Dashboard'],
                    ['admin.customers.index', 'Customers'],
                    ['admin.loans.index', 'Loans'],
                    ['admin.featured-loans.index', 'Featured loans'],
                    ['admin.payments.index', 'Payments'],
                    ['admin.support-email.edit', 'Support email'],
                ] as [$route, $label])
                    <a href="{{ route($route) }}" class="rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs(str_replace('.index', '.*', $route)) || request()->routeIs($route) ? 'bg-white/15' : 'text-white/80 hover:bg-white/10' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
            <form method="POST" action="{{ route('admin.logout') }}" class="p-4">
                @csrf
                <button class="w-full rounded-xl bg-white/10 px-4 py-3 text-sm font-semibold hover:bg-white/20">Logout</button>
            </form>
        </aside>

        <div class="min-w-0">
            <header class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-4 lg:px-8">
                <button class="rounded-xl border border-slate-200 p-2 lg:hidden" @click="open = true" type="button">Menu</button>
                <h1 class="text-lg font-bold">{{ $heading ?? 'Dashboard' }}</h1>
                <p class="text-sm text-muted">{{ auth()->user()->name }}</p>
            </header>

            <div class="fixed inset-0 z-40 bg-black/40 lg:hidden" x-show="open" x-cloak @click="open = false"></div>
            <aside class="fixed inset-y-0 left-0 z-50 w-72 brand-gradient p-4 text-white lg:hidden" x-show="open" x-cloak>
                <p class="mb-4 text-lg font-extrabold">MaxWallet</p>
                <nav class="space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="block rounded-xl bg-white/10 px-4 py-3">Dashboard</a>
                    <a href="{{ route('admin.customers.index') }}" class="block rounded-xl px-4 py-3">Customers</a>
                    <a href="{{ route('admin.loans.index') }}" class="block rounded-xl px-4 py-3">Loans</a>
                    <a href="{{ route('admin.featured-loans.index') }}" class="block rounded-xl px-4 py-3">Featured loans</a>
                    <a href="{{ route('admin.payments.index') }}" class="block rounded-xl px-4 py-3">Payments</a>
                    <a href="{{ route('admin.support-email.edit') }}" class="block rounded-xl px-4 py-3">Support email</a>
                </nav>
            </aside>

            <main class="min-w-0 px-4 py-6 lg:px-8">
                @if (session('success'))
                    <div class="mb-4 rounded-2xl bg-brand-soft px-4 py-3 text-sm font-medium text-brand">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-4 rounded-2xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
                @endif
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
