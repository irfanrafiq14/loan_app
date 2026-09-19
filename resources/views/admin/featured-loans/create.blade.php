<x-layouts.admin title="Add featured loan" heading="Add featured loan">
    <form method="POST" action="{{ route('admin.featured-loans.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-4 rounded-3xl bg-white p-6 shadow-sm">
        @include('admin.featured-loans._form', ['loan' => null])
        <button class="rounded-2xl brand-gradient px-5 py-3 text-sm font-bold text-white">Save featured loan</button>
    </form>
</x-layouts.admin>
