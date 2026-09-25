<x-layouts.admin title="Payments" heading="Payment review">
    <form method="GET" class="mb-4 flex gap-3">
        <select name="status" class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
            <option value="">All statuses</option>
            @foreach (['pending', 'completed', 'rejected'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button class="rounded-2xl bg-white px-4 py-3 font-bold shadow-sm">Filter</button>
    </form>
    <div class="rounded-3xl bg-white shadow-sm">
        <div class="admin-table-scroll overflow-x-auto">
        <table class="min-w-[40rem] whitespace-nowrap text-left text-sm">
            <thead class="bg-slate-50 text-muted">
                <tr>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Loan</th>
                    <th class="px-4 py-3">Transaction</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $payment)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-semibold">{{ $payment->user->name }}</td>
                        <td class="px-4 py-3">{{ $payment->loan->title }}</td>
                        <td class="px-4 py-3">{{ $payment->transaction_id }}</td>
                        <td class="px-4 py-3">{{ $payment->status->label() }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.payments.show', $payment) }}" class="font-semibold text-brand">Review</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="p-4">{{ $payments->links() }}</div>
    </div>
</x-layouts.admin>
