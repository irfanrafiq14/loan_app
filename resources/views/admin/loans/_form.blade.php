@csrf
<div>
    <label class="mb-2 block text-sm font-semibold">Customer</label>
    <select name="user_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
        <option value="">Select customer</option>
        @foreach ($customers as $customer)
            <option value="{{ $customer->id }}" @selected((int) old('user_id', $loan->user_id ?? $selected ?? 0) === $customer->id)>{{ $customer->name }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="mb-2 block text-sm font-semibold">Loan title</label>
    <input name="title" value="{{ old('title', $loan->title ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
</div>
<div class="grid gap-4 sm:grid-cols-3">
    <div>
        <label class="mb-2 block text-sm font-semibold">Loan amount</label>
        <input name="amount" type="number" step="0.01" value="{{ old('amount', $loan->amount ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
    </div>
    <div>
        <label class="mb-2 block text-sm font-semibold">Minimum amount</label>
        <input name="minimum_amount" type="number" step="0.01" value="{{ old('minimum_amount', $loan->minimum_amount ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
    </div>
    <div>
        <label class="mb-2 block text-sm font-semibold">Maximum amount</label>
        <input name="maximum_amount" type="number" step="0.01" value="{{ old('maximum_amount', $loan->maximum_amount ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
    </div>
</div>
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-2 block text-sm font-semibold">Loan date</label>
        <input name="loan_date" type="date" value="{{ old('loan_date', isset($loan) ? optional($loan->loan_date)->format('Y-m-d') : '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
    </div>
    <div>
        <label class="mb-2 block text-sm font-semibold">Due date</label>
        <input name="due_date" type="date" value="{{ old('due_date', isset($loan) ? optional($loan->due_date)->format('Y-m-d') : '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
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
<div>
    <label class="mb-2 block text-sm font-semibold">Status</label>
    <select name="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
        @foreach (['pending', 'completed'] as $status)
            <option value="{{ $status }}" @selected(old('status', $loan->status->value ?? 'pending') === $status)>{{ ucfirst($status) }}</option>
        @endforeach
    </select>
</div>
@if ($errors->any())
    <div class="rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ $errors->first() }}
    </div>
@endif
