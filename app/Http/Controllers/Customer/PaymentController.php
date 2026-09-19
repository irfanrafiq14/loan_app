<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StorePaymentRequest;
use App\Models\Loan;
use App\Models\PaymentMethod;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments)
    {
    }

    public function create(Loan $loan): View|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('view', $loan);

        if (! $loan->canAcceptPayment()) {
            return redirect()
                ->route('orders')
                ->with('error', 'This loan already has a payment in review or is not payable.');
        }

        $methods = PaymentMethod::query()->active()->get();

        return view('customer.pay', compact('loan', 'methods'));
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $loan = Loan::query()->findOrFail($request->integer('loan_id'));

        $this->authorize('pay', $loan);

        try {
            $this->payments->submit(
                $request->user(),
                $loan,
                $request->only(['payment_method_id', 'transaction_id']),
                $request->file('screenshot')
            );
        } catch (RuntimeException $exception) {
            $message = match ($exception->getMessage()) {
                'not_payable' => 'A payment is already pending or this loan cannot accept payments.',
                'invalid_file' => 'Only JPG, JPEG, PNG, and WebP images are allowed.',
                default => 'We could not submit this payment. Please try again.',
            };

            return back()->with('error', $message)->withInput();
        }

        return redirect()
            ->route('orders', ['tab' => 'pending'])
            ->with('success', 'Payment submitted. We will review your screenshot shortly.');
    }
}
