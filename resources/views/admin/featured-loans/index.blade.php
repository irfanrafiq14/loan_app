<x-layouts.admin title="Featured loans" heading="Featured loans">
    <div class="mb-4 flex items-center justify-between gap-3">
        <p class="text-sm text-muted">These offers appear on every customer home page. Add, edit, or delete them here.</p>
        <a href="{{ route('admin.featured-loans.create') }}" class="shrink-0 rounded-2xl brand-gradient px-4 py-3 text-sm font-bold text-white">Add featured loan</a>
    </div>

    <div class="overflow-hidden rounded-3xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-muted">
                <tr>
                    <th class="px-4 py-3">Image</th>
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($loans as $loan)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3">
                            <img src="{{ $loan->featuredImageUrl() }}" alt="{{ $loan->title }}" class="h-12 w-12 rounded-xl object-cover">
                        </td>
                        <td class="px-4 py-3 font-semibold">{{ $loan->title }}</td>
                        <td class="px-4 py-3">{{ \App\Support\Money::format($loan->amount) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.featured-loans.edit', $loan) }}" class="font-semibold text-brand">Edit</a>
                                <form method="POST" action="{{ route('admin.featured-loans.destroy', $loan) }}" onsubmit="return confirm('Delete this featured loan?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="font-semibold text-red-600">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-muted">No featured loans yet. Add one to show it on the customer home page.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin>
