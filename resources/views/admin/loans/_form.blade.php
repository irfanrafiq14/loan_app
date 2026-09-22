@csrf
<div>
    <label class="mb-2 block text-sm font-semibold">Customer</label>
    <select name="user_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
        <option value="">Select customer</option>
        @foreach ($customers as $customer)
            <option value="{{ $customer->id }}" @selected((int) old('user_id', $loan->user_id ?? $selected ?? 0) === $customer->id)>{{ $customer->name }}</option>
        @endforeach
    </select>
    @error('user_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
<div>
    <label class="mb-2 block text-sm font-semibold">Loan title</label>
    <input name="title" value="{{ old('title', $loan->title ?? '') }}" placeholder="Sweet Money" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-2 block text-sm font-semibold">Total due (₹)</label>
        <input name="total_due" type="number" step="0.01" value="{{ old('total_due', $loan->total_due ?? '') }}" placeholder="5250" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
        <p class="mt-1 text-xs text-muted">Shown as Total due on the customer card.</p>
        @error('total_due') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="mb-2 block text-sm font-semibold">Loan amount (₹)</label>
        <input name="amount" type="number" step="0.01" value="{{ old('amount', $loan->amount ?? '') }}" placeholder="2750" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
        <p class="mt-1 text-xs text-muted">Shown under View Details.</p>
        @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-2 block text-sm font-semibold">Loan date</label>
        <input name="loan_date" type="date" value="{{ old('loan_date', isset($loan) ? optional($loan->loan_date)->format('Y-m-d') : '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
        @error('loan_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="mb-2 block text-sm font-semibold">Due date</label>
        <input name="due_date" type="date" value="{{ old('due_date', isset($loan) ? optional($loan->due_date)->format('Y-m-d') : '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
        @error('due_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
@if ($errors->any())
    <div class="rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ $errors->first() }}
    </div>
@endif

