<x-layouts.admin title="Payment methods" heading="Payment methods">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('admin.payment-methods.create') }}" class="rounded-2xl brand-gradient px-4 py-3 text-sm font-bold text-white">Add method</a>
    </div>
    <p class="mb-4 text-sm text-muted">These labels appear on the customer payment page. They are not live payment-provider integrations.</p>
    <div class="space-y-3">
        @foreach ($methods as $method)
            <article class="flex items-center justify-between rounded-3xl bg-white px-5 py-4 shadow-sm">
                <div>
                    <p class="font-bold">{{ $method->name }}</p>
                    <p class="text-sm text-muted">{{ $method->account_number }} · {{ $method->is_active ? 'Active' : 'Inactive' }}</p>
                </div>
                <a href="{{ route('admin.payment-methods.edit', $method) }}" class="font-semibold text-brand">Edit</a>
            </article>
        @endforeach
    </div>
</x-layouts.admin>
