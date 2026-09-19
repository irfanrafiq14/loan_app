<x-layouts.admin title="Login links" heading="Login links">
    @if (session('generated_url'))
        <div class="mb-4 rounded-3xl border border-brand/20 bg-brand-soft p-4" x-data="{ copied: false }">
            <p class="text-sm font-semibold text-brand">Generated login link (shown once)</p>
            <div class="mt-2 flex flex-col gap-2 sm:flex-row">
                <input x-ref="link" readonly value="{{ session('generated_url') }}" class="w-full rounded-2xl bg-white px-4 py-3 text-sm">
                <button type="button" class="rounded-2xl brand-gradient px-4 py-3 text-sm font-bold text-white" @click="navigator.clipboard.writeText($refs.link.value); copied = true">
                    <span x-text="copied ? 'Copied' : 'Copy'"></span>
                </button>
            </div>
        </div>
    @endif

    <div class="mb-4 flex justify-end">
        <a href="{{ route('admin.login-links.create') }}" class="rounded-2xl brand-gradient px-4 py-3 text-sm font-bold text-white">Create login link</a>
    </div>

    <div class="overflow-hidden rounded-3xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-muted">
                <tr>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">App</th>
                    <th class="px-4 py-3">Expires</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($links as $link)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-semibold">{{ $link->user->name }}</td>
                        <td class="px-4 py-3">{{ $link->app_name }}</td>
                        <td class="px-4 py-3">{{ $link->expires_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $link->statusLabel() }}</td>
                        <td class="px-4 py-3 text-right">
                            @if ($link->isValid())
                                <form method="POST" action="{{ route('admin.login-links.revoke', $link) }}">
                                    @csrf
                                    <button class="font-semibold text-red-600">Revoke</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $links->links() }}</div>
    </div>
</x-layouts.admin>
