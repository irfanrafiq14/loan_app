<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentMethodRequest;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function index(): View
    {
        $methods = PaymentMethod::query()->orderBy('sort_order')->get();

        return view('admin.payment-methods.index', compact('methods'));
    }

    public function create(): View
    {
        return view('admin.payment-methods.create');
    }

    public function store(StorePaymentMethodRequest $request): RedirectResponse
    {
        PaymentMethod::query()->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->integer('sort_order'),
        ]);

        return redirect()
            ->route('admin.payment-methods.index')
            ->with('success', 'Payment method created.');
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        return view('admin.payment-methods.edit', compact('paymentMethod'));
    }

    public function update(StorePaymentMethodRequest $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->integer('sort_order'),
        ]);

        return redirect()
            ->route('admin.payment-methods.index')
            ->with('success', 'Payment method updated.');
    }
}
