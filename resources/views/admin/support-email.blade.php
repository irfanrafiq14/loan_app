<x-layouts.admin title="Support email" heading="Support email">
    <p class="mb-4 max-w-xl text-sm text-muted">The customer Support button opens Gmail with this address already filled in.</p>

    <form method="POST" action="{{ route('admin.support-email.update') }}" class="max-w-xl space-y-4 rounded-3xl bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <div>
            <label class="mb-2 block text-sm font-semibold">Support email</label>
            <input name="support_email" type="email" value="{{ old('support_email', $supportEmail) }}" placeholder="support@example.com" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
            @error('support_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <button class="rounded-2xl brand-gradient px-5 py-3 text-sm font-bold text-white">Save support email</button>
    </form>
</x-layouts.admin>
