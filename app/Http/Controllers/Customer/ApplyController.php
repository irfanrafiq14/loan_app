<?php

namespace App\Http\Controllers\Customer;

use App\Enums\LoanStatus;
use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApplyController extends Controller
{
    public function show(Loan $loan): RedirectResponse
    {
        $this->authorize('apply', $loan);

        return redirect()->route('home');
    }

    public function store(Request $request, Loan $loan): JsonResponse|RedirectResponse
    {
        $this->authorize('apply', $loan);

        $customer = $request->user();

        if ($customer->hasOutstandingDues()) {
            $message = 'Clear your previous dues before applying.';

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->route('home')->with('error', $message);
        }

        $minimum = (float) ($loan->minimum_amount ?? 0);
        $maximum = (float) ($loan->maximum_amount ?: $loan->amount);
        $amount = (float) ($request->input('amount') ?: $loan->amount);

        if ($maximum < $minimum) {
            [$minimum, $maximum] = [$maximum, $minimum];
        }

        if ($maximum > 0) {
            $amount = min(max($amount, $minimum), $maximum);
        }

        $application = Loan::query()->create([
            'user_id' => $customer->id,
            'title' => $loan->title,
            'amount' => $amount,
            'total_due' => $amount,
            'minimum_amount' => $loan->minimum_amount,
            'maximum_amount' => $loan->maximum_amount,
            'loan_date' => now()->toDateString(),
            'due_date' => $loan->due_date,
            'description' => $loan->description,
            'payment_instructions' => $loan->payment_instructions,
            'status' => LoanStatus::Pending,
            'is_featured' => false,
        ]);

        $message = 'Processing...';

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('home')->with('success', $message);
    }
}
