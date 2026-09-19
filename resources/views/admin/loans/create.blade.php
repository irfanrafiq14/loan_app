<x-layouts.admin title="Create loan" heading="Create loan">
    <form method="POST" action="{{ route('admin.loans.store') }}" class="max-w-2xl space-y-4 rounded-3xl bg-white p-6 shadow-sm">
        @include('admin.loans._form', ['loan' => null, 'selected' => $selected])
        <button class="rounded-2xl brand-gradient px-5 py-3 text-sm font-bold text-white">Save loan</button>
    </form>
</x-layouts.admin>
