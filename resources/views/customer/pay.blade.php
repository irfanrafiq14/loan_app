<x-layouts.customer :title="'Pay · '.$appName">
    <div class="px-5 pt-6" x-data="paymentForm({{ old('payment_method_id', $methods->first()?->id ?? 'null') }})">
        <a href="{{ route('orders') }}" class="text-sm font-semibold text-brand">← Back to orders</a>
        <p class="mt-2 text-xs font-semibold text-brand">{{ $appName }}</p>
        <h1 class="mt-1 text-2xl font-extrabold">Pay {{ $loan->title }}</h1>
        <p class="text-sm text-muted">{{ $loan->reference() }} · {{ \App\Support\Money::format($loan->amount) }}</p>

        <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data" class="mt-6 space-y-5" @submit="validate($event)">
            @csrf
            <input type="hidden" name="loan_id" value="{{ $loan->id }}">

            <section class="rounded-[24px] border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-brand">Step 1 · Payment link</p>
                <h2 class="mt-2 font-bold">{{ $methods->first()?->name ?? 'Bank transfer' }}</h2>
                <p class="mt-1 text-sm text-muted">{{ $loan->payment_instructions ?: ($methods->first()?->instructions ?? 'Transfer the loan amount using one of the methods below, then upload your receipt.') }}</p>
                <div class="mt-3 flex items-center justify-between rounded-2xl bg-brand-soft px-4 py-3">
                    <div>
                        <p class="text-xs text-muted">Payment account number</p>
                        <p class="font-bold" x-ref="account">{{ $methods->first()?->account_number ?? '—' }}</p>
                    </div>
                    <button type="button" class="rounded-xl bg-white px-3 py-2 text-xs font-bold text-brand" @click="copy($refs.account.textContent)">Copy</button>
                </div>
            </section>

            <section class="rounded-[24px] border border-slate-100 bg-white p-4 shadow-sm" :data-first-error="errors.method ? true : null">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-brand">Step 2 · Select payment method</p>
                    <span class="rounded-full bg-brand px-3 py-1 text-[11px] font-bold text-white">Required</span>
                </div>
                <p class="mt-1 text-xs text-muted">These are transfer options configured by {{ $appName }}. No live payment provider is connected.</p>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    @foreach ($methods as $method)
                        <label class="rounded-2xl border p-3" :class="method == {{ $method->id }} ? 'border-brand bg-brand-soft' : 'border-slate-200'">
                            <input type="radio" name="payment_method_id" value="{{ $method->id }}" class="sr-only" x-model="method" @if ($loop->first) checked @endif>
                            <p class="font-bold">{{ $method->name }}</p>
                            <p class="mt-1 text-xs text-muted">{{ $method->account_number }}</p>
                        </label>
                    @endforeach
                </div>
                @error('payment_method_id') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                <p class="mt-2 text-sm font-semibold text-red-600" x-show="errors.method" x-cloak x-text="errors.method"></p>
            </section>

            <section class="rounded-[24px] border border-slate-100 bg-white p-4 shadow-sm" :data-first-error="errors.transaction ? true : null">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-brand">Step 3 · Enter transaction ID</p>
                    <span class="rounded-full bg-brand px-3 py-1 text-[11px] font-bold text-white">Required</span>
                </div>
                <input type="text" name="transaction_id" value="{{ old('transaction_id') }}" placeholder="e.g. TXN-982341" required class="mt-3 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-brand">
                @error('transaction_id') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                <p class="mt-2 text-sm font-semibold text-red-600" x-show="errors.transaction" x-cloak x-text="errors.transaction"></p>
            </section>

            <section class="rounded-[24px] border-2 border-brand bg-white p-5 shadow-md" :data-first-error="errors.screenshot ? true : null">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-extrabold uppercase tracking-wide text-brand">Step 4 · Upload screenshot</p>
                    <span class="rounded-full bg-brand px-3 py-1 text-[11px] font-bold text-white">Required</span>
                </div>
                <p class="mt-2 text-sm font-semibold text-ink">Add a clear photo of your payment receipt.</p>
                <label class="mt-4 flex min-h-52 cursor-pointer flex-col items-center justify-center rounded-[22px] border-2 border-dashed border-brand bg-brand-soft px-4 py-6 text-center"
                       @dragover.prevent
                       @drop.prevent="onDrop($event)">
                    <input type="file" name="screenshot" x-ref="screenshot" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required class="sr-only" @change="preview($event)">
                    <template x-if="!image">
                        <div>
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl brand-gradient text-white shadow-md">
                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1M12 4v12m0-12 4 4m-4-4L8 8"/></svg>
                            </div>
                            <p class="mt-4 text-base font-extrabold text-brand">Tap to upload screenshot</p>
                            <p class="mt-1 text-sm font-semibold text-ink">or drop the image here</p>
                            <p class="mt-3 text-xs text-muted">JPG, JPEG, PNG, or WebP · Maximum 5 MB</p>
                        </div>
                    </template>
                    <template x-if="image">
                        <div class="w-full">
                            <img :src="image" alt="Payment screenshot preview" class="mx-auto max-h-64 w-full rounded-2xl object-cover">
                            <p class="mt-3 truncate text-sm font-bold" x-text="fileName"></p>
                            <p class="mt-1 text-xs font-semibold text-brand">Tap to replace this image</p>
                        </div>
                    </template>
                </label>
                <button type="button" class="mt-3 w-full text-sm font-bold text-red-600" x-show="image" x-cloak @click.prevent="remove()">Remove image</button>
                @error('screenshot') <p class="mt-3 rounded-2xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                <p class="mt-3 rounded-2xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-600" x-show="errors.screenshot" x-cloak x-text="errors.screenshot"></p>
            </section>

            <button class="w-full rounded-2xl brand-gradient py-3.5 text-sm font-bold text-white">Submit payment</button>
        </form>
    </div>
</x-layouts.customer>
