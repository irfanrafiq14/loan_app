<x-layouts.admin title="Customers" heading="Customers">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex-1">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by name or phone" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3">
        </form>
        <a href="{{ route('admin.customers.create') }}" class="rounded-2xl brand-gradient px-4 py-3 text-center text-sm font-bold text-white">Create customer</a>
    </div>
    <div class="rounded-3xl border border-slate-100 bg-white">
        <div class="admin-table-scroll overflow-x-auto">
        <table class="min-w-[40rem] whitespace-nowrap text-left text-sm">
            <thead class="bg-slate-50 text-muted">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">App</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customers as $customer)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-semibold">{{ $customer->name }}</td>
                        <td class="px-4 py-3">{{ $customer->displayPhone() }}</td>
                        <td class="px-4 py-3">{{ $customer->brandedName() ?: '—' }}</td>
                        <td class="px-4 py-3 capitalize">{{ $customer->status->value }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="font-semibold text-brand">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="p-4">{{ $customers->links() }}</div>
    </div>
</x-layouts.admin>
