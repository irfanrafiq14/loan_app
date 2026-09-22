@php
    $methodsJson = $methods->map(fn ($method) => [
        'id' => $method->id,
        'name' => $method->name,
        'logo' => $method->logoUrl(),
    ])->values();
    $selectedMethod = old('payment_method_id', $methods->first()?->id);
@endphp

<x-layouts.customer :title="'Make Payment · '.$appName">
    <div class="px-5 pt-5 lg:px-0 lg:pt-0" x-data="paymentForm({{ $selectedMethod ? (int) $selectedMethod : 'null' }}, @js($paymentLink), {{ \Illuminate\Support\Js::from($methodsJson) }})">
        <div class="flex items-center gap-3">
            <a href="{{ route('orders') }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm" aria-label="Back">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19 8 12l7-7"/></svg>
            </a>
            <div>
                <p class="hidden text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400 lg:block">Payments / Make payment</p>
                <h1 class="text-xl font-extrabold">Make Payment</h1>
            </div>
        </div>

        <section class="hero-gradient relative mt-5 overflow-hidden rounded-[28px] p-5 text-white shadow-lg">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-white/80">Total pending amount</p>
            <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-4xl font-extrabold">{{ \App\Support\Money::format($loan->totalDueAmount()) }}</p>
                    <p class="mt-2 inline-flex items-center gap-2 text-sm text-white/85">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 3v2m8-2v2M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/></svg>
                        Next due: {{ optional($loan->due_date)->format('jS M Y') ?? '—' }}
                    </p>
                </div>
                <div class="rounded-2xl bg-white/15 px-4 py-3 text-right">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-white/80">Loan account</p>
                    <p class="mt-1 font-bold">Ref: {{ $loan->reference() }}</p>
                </div>
            </div>
        </section>

        <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data" class="mt-6 space-y-7" @submit="validate($event)">
            @csrf
            <input type="hidden" name="loan_id" value="{{ $loan->id }}">

            <section>
                <h2 class="text-base font-extrabold">Step 1: Payment link</h2>
                <p class="mt-1 text-sm text-muted">For {{ $loan->title }}, open or copy this link to pay in your lender’s portal. You can also use your UPI app in the steps below if you pay that way.</p>
                <div class="mt-3 flex items-center gap-3 rounded-2xl bg-white px-4 py-3 shadow-sm">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 9.5A2.5 2.5 0 0 1 6.5 7H19v11H6.5A2.5 2.5 0 0 1 4 15.5v-6Z"/></svg>
                    </span>
                    <p class="min-w-0 flex-1 truncate font-bold" x-text="currentLink || 'No payment link yet'"></p>
                    <button type="button" class="inline-flex shrink-0 items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-bold uppercase tracking-wide text-white" @click="copyLink()">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Zm2-3h6a2 2 0 0 1 2 2v1"/></svg>
                        <span x-text="copied ? 'Copied' : 'Copy link'"></span>
                    </button>
                </div>
                <p class="mt-2 text-xs text-muted">Only an administrator can change this link.</p>
            </section>

            <section :data-first-error="errors.method ? true : null">
                <h2 class="text-base font-extrabold">Step 2: Select Payment Method</h2>
                <p class="mt-1 text-sm text-muted">Choose the application you have installed on your mobile device.</p>
                <div class="mt-3 grid grid-cols-2 gap-3 lg:grid-cols-4">
                    @foreach ($methods as $method)
                        <label class="method-card" :class="method == {{ $method->id }} ? 'is-active' : ''">
                            <input type="radio" name="payment_method_id" value="{{ $method->id }}" class="sr-only" x-model.number="method" @if ((int) $selectedMethod === $method->id) checked @endif>
                            <span class="method-card-check">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="m5 12 5 5L20 7"/></svg>
                            </span>
                            <img src="{{ $method->logoUrl() }}" alt="{{ $method->name }}" class="method-logo-image">
                        </label>
                    @endforeach
                </div>
                @error('payment_method_id') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                <p class="mt-2 text-sm font-semibold text-red-600" x-show="errors.method" x-cloak x-text="errors.method"></p>
            </section>

            <section :data-first-error="errors.transaction ? true : null">
                <h2 class="text-base font-extrabold">Step 3: Enter Transaction ID</h2>
                <p class="mt-1 text-sm text-muted">After payment, enter the UTR / transaction reference from your app.</p>
                <div class="relative mt-3">
                    <input type="text" name="transaction_id" value="{{ old('transaction_id') }}" placeholder="e.g. 1234 5678 9012" minlength="12" maxlength="120" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3.5 pr-40 outline-none focus:border-brand">
                    <span class="pointer-events-none absolute right-4 top-1/2 hidden -translate-y-1/2 text-[11px] font-bold uppercase tracking-wide text-slate-400 lg:inline">12 digits required</span>
                </div>
                @error('transaction_id') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                <p class="mt-2 text-sm font-semibold text-red-600" x-show="errors.transaction" x-cloak x-text="errors.transaction"></p>
            </section>

            <section class="rounded-[24px] border-2 border-dashed border-brand/40 bg-white p-5" :data-first-error="errors.screenshot ? true : null">
                <h2 class="text-base font-extrabold">Step 4: Upload screenshot</h2>
                <p class="mt-1 text-sm text-muted">Add a clear photo of your payment receipt.</p>
                <label class="mt-4 flex min-h-44 cursor-pointer flex-col items-center justify-center rounded-[22px] bg-brand-soft px-4 py-6 text-center"
                       @dragover.prevent
                       @drop.prevent="onDrop($event)">
                    <input type="file" name="screenshot" x-ref="screenshot" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required class="sr-only" @change="preview($event)">
                    <template x-if="!image">
                        <div>
                            <p class="text-base font-extrabold text-brand">Tap to upload screenshot</p>
                            <p class="mt-1 text-xs text-muted">JPG, JPEG, PNG, or WebP · Maximum 5 MB</p>
                        </div>
                    </template>
                    <template x-if="image">
                        <div class="w-full">
                            <img :src="image" alt="Payment screenshot preview" class="mx-auto max-h-64 w-full rounded-2xl object-cover">
                            <p class="mt-3 truncate text-sm font-bold" x-text="fileName"></p>
                        </div>
                    </template>
                </label>
                <button type="button" class="mt-3 w-full text-sm font-bold text-red-600" x-show="image" x-cloak @click.prevent="remove()">Remove image</button>
                @error('screenshot') <p class="mt-3 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                <p class="mt-3 text-sm font-semibold text-red-600" x-show="errors.screenshot" x-cloak x-text="errors.screenshot"></p>
            </section>

            <button class="btn-primary">
                Submit UTR
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5-5 5M6 12h12"/></svg>
            </button>
            <p class="text-center text-xs text-muted">By clicking submit, you agree to our Terms of Service regarding protected transactions.</p>
        </form>
    </div>
</x-layouts.customer>
