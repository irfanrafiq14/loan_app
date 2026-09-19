@php($method = $method ?? null)
<div>
    <label class="mb-2 block text-sm font-semibold">Name</label>
    <input name="name" value="{{ old('name', $method->name ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
</div>
<div>
    <label class="mb-2 block text-sm font-semibold">Account number</label>
    <input name="account_number" value="{{ old('account_number', $method->account_number ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
</div>
<div>
    <label class="mb-2 block text-sm font-semibold">Instructions</label>
    <textarea name="instructions" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('instructions', $method->instructions ?? '') }}</textarea>
</div>
<div>
    <label class="mb-2 block text-sm font-semibold">Sort order</label>
    <input name="sort_order" type="number" value="{{ old('sort_order', $method->sort_order ?? 0) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
</div>
<label class="flex items-center gap-2 text-sm font-semibold">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $method->is_active ?? true))>
    Active
</label>
