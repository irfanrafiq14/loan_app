<x-layouts.admin title="Edit loan" heading="Edit loan">
    <form method="POST" action="{{ route('admin.loans.update', $loan) }}" class="max-w-2xl space-y-4 rounded-3xl bg-white p-6 shadow-sm">
        @method('PUT')
        @include('admin.loans._form', ['selected' => $loan->user_id])
        <button class="rounded-2xl brand-gradient px-5 py-3 text-sm font-bold text-white">Update loan</button>
    </form>
</x-layouts.admin>
