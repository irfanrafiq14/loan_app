<x-layouts.admin title="Loans" heading="Loans">
    <form method="GET" class="mb-4 grid gap-3 sm:grid-cols-3">
        <select name="customer" class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
            <option value="">All customers</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" @selected(request('customer') == $customer->id)>{{ $customer->name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
            <option value="">All statuses</option>
            @foreach (['pending', 'completed'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button class="rounded-2xl bg-white px-4 py-3 font-bold shadow-sm">Filter</button>
    </form>
    <div class="mb-4 flex justify-end">
        <a href="{{ route('admin.loans.create') }}" class="rounded-2xl brand-gradient px-4 py-3 text-sm font-bold text-white">Create loan</a>
    </div>
    <div class="rounded-3xl bg-white shadow-sm">
        <div class="admin-table-scroll overflow-x-auto">
        <table class="min-w-[40rem] whitespace-nowrap text-left text-sm">
            <thead class="bg-slate-50 text-muted">
                <tr>
                    <th class="px-4 py-3">Loan</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Total due</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($loans as $loan)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-semibold">{{ $loan->title }}<div class="text-xs text-muted">{{ $loan->reference() }}</div></td>
                        <td class="px-4 py-3">{{ $loan->user->name }}</td>
                        <td class="px-4 py-3">{{ \App\Support\Money::format($loan->totalDueAmount()) }}</td>
                        <td class="px-4 py-3">{{ $loan->status->label() }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.loans.show', $loan) }}" class="font-semibold text-brand">View</a>
                                <a href="{{ route('admin.loans.edit', $loan) }}" class="font-semibold text-brand">Edit</a>
                                @if ($loan->canBeDeleted())
                                    <form method="POST" action="{{ route('admin.loans.destroy', $loan) }}" onsubmit="return confirm('Delete this loan?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-semibold text-red-600">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="p-4">{{ $loans->links() }}</div>
    </div>
</x-layouts.admin>
