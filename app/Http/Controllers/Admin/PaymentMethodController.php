<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePaymentLinkRequest;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function edit(): View
    {
        PaymentMethod::syncSharedLink();

        return view('admin.payment-link', [
            'paymentLink' => PaymentMethod::sharedLink(),
        ]);
    }

    public function update(UpdatePaymentLinkRequest $request): RedirectResponse
    {
        PaymentMethod::syncSharedLink($request->string('payment_link')->toString());

        return redirect()
            ->route('admin.payment-link.edit')
            ->with('success', 'Payment link updated. Customers can copy it on the pay page.');
    }
}
