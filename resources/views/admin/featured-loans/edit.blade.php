<x-layouts.admin title="Edit featured loan" heading="Edit featured loan">
    <form method="POST" action="{{ route('admin.featured-loans.update', $loan) }}" enctype="multipart/form-data" class="max-w-2xl space-y-4 rounded-3xl bg-white p-6 shadow-sm">
        @method('PUT')
        @include('admin.featured-loans._form')
        <button class="rounded-2xl brand-gradient px-5 py-3 text-sm font-bold text-white">Update featured loan</button>
    </form>
    <form method="POST" action="{{ route('admin.featured-loans.destroy', $loan) }}" class="mt-4 max-w-2xl" onsubmit="return confirm('Delete this featured loan?')">
        @csrf
        @method('DELETE')
        <button class="rounded-2xl bg-red-50 px-5 py-3 text-sm font-bold text-red-600">Delete featured loan</button>
    </form>
</x-layouts.admin>
