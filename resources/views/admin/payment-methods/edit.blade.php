<x-layouts.admin title="Edit payment method" heading="Edit payment method">
    <form method="POST" action="{{ route('admin.payment-methods.update', $paymentMethod) }}" class="max-w-xl space-y-4 rounded-3xl bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('admin.payment-methods._form', ['method' => $paymentMethod])
        <button class="rounded-2xl brand-gradient px-5 py-3 text-sm font-bold text-white">Update method</button>
    </form>
</x-layouts.admin>
