<x-layouts.admin title="Add payment method" heading="Add payment method">
    <form method="POST" action="{{ route('admin.payment-methods.store') }}" class="max-w-xl space-y-4 rounded-3xl bg-white p-6 shadow-sm">
        @csrf
        @include('admin.payment-methods._form')
        <button class="rounded-2xl brand-gradient px-5 py-3 text-sm font-bold text-white">Save method</button>
    </form>
</x-layouts.admin>
