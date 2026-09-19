<x-layouts.customer :title="'Profile · '.$appName">
    <div class="px-5 pt-8 text-center">
        <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full brand-gradient text-3xl font-extrabold text-white">
            {{ strtoupper(substr($customer->name, 0, 1)) }}
        </div>
        <h1 class="mt-4 text-2xl font-extrabold">{{ $customer->name }}</h1>
        <p class="mt-1 text-sm text-muted">{{ $customer->maskedPhone() }}</p>
    </div>

    <div class="mx-5 mt-8 rounded-[24px] border border-slate-100 bg-white p-5 shadow-sm">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="w-full rounded-2xl bg-red-50 py-3 text-sm font-bold text-red-600">Logout</button>
        </form>
        <p class="mt-5 text-center text-xs text-muted">{{ $appName }} v1.0.0</p>
        <p class="mt-2 text-center text-xs text-muted">For account help, contact your {{ $appName }} administrator. Never share your login link.</p>
    </div>
</x-layouts.customer>
