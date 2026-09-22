@csrf
<div class="rounded-[24px] border-2 border-brand bg-brand-soft/60 p-5" x-data="{
    image: @js($loan?->featuredImageUrl()),
    fileName: null,
    preview(event) {
        const file = event.target.files[0];
        if (!file) return;
        this.image = URL.createObjectURL(file);
        this.fileName = file.name;
    }
}">
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm font-extrabold uppercase tracking-wide text-brand">Feature image</p>
        <span class="rounded-full bg-brand px-3 py-1 text-[11px] font-bold text-white">Shown on home</span>
    </div>
    <p class="mt-2 text-sm font-semibold">Upload a clear image for this featured loan. Customers see it on the home page.</p>
    <label class="mt-4 flex min-h-52 cursor-pointer flex-col items-center justify-center rounded-[22px] border-2 border-dashed border-brand bg-white px-4 py-6 text-center">
        <input type="file" name="featured_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="sr-only" @change="preview($event)">
        <template x-if="!image">
            <div>
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl brand-gradient text-white shadow-md">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1M12 4v12m0-12 4 4m-4-4L8 8"/></svg>
                </div>
                <p class="mt-4 text-base font-extrabold text-brand">Tap to upload feature image</p>
                <p class="mt-1 text-sm font-semibold">or choose a file from your computer</p>
                <p class="mt-3 text-xs text-muted">JPG, JPEG, PNG, or WebP · Maximum 5 MB</p>
            </div>
        </template>
        <template x-if="image">
            <div class="w-full">
                <img :src="image" alt="Feature image preview" class="mx-auto max-h-64 w-full rounded-2xl object-cover">
                <p class="mt-3 truncate text-sm font-bold" x-text="fileName || 'Current feature image'"></p>
                <p class="mt-1 text-xs font-semibold text-brand">Tap to replace this image</p>
            </div>
        </template>
    </label>
</div>
<div>
    <label class="mb-2 block text-sm font-semibold">Loan title</label>
    <input name="title" value="{{ old('title', $loan->title ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
</div>
<div class="grid gap-4 sm:grid-cols-3">
    <div>
        <label class="mb-2 block text-sm font-semibold">Loan amount (₹)</label>
        <input name="amount" type="number" step="0.01" value="{{ old('amount', $loan->amount ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
    </div>
    <div>
        <label class="mb-2 block text-sm font-semibold">Minimum amount (₹)</label>
        <input name="minimum_amount" type="number" step="0.01" value="{{ old('minimum_amount', $loan->minimum_amount ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
    </div>
    <div>
        <label class="mb-2 block text-sm font-semibold">Maximum amount (₹)</label>
        <input name="maximum_amount" type="number" step="0.01" value="{{ old('maximum_amount', $loan->maximum_amount ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
    </div>
</div>
<div>
    <label class="mb-2 block text-sm font-semibold">Description</label>
    <textarea name="description" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('description', $loan->description ?? '') }}</textarea>
</div>
<div>
    <label class="mb-2 block text-sm font-semibold">Payment instructions</label>
    <textarea name="payment_instructions" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('payment_instructions', $loan->payment_instructions ?? '') }}</textarea>
</div>
@if ($errors->any())
    <div class="rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ $errors->first() }}
    </div>
@endif
