<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewPaymentRequest;
use App\Http\Requests\Admin\UpdatePaymentScreenshotRequest;
use App\Models\LoanPayment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', LoanPayment::class);

        $payments = LoanPayment::query()
            ->with(['user', 'loan', 'paymentMethod'])
            ->when($request->string('status')->toString(), fn($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    public function show(LoanPayment $payment): View
    {
        $this->authorize('view', $payment);

        $payment->load(['user', 'loan', 'paymentMethod']);
        $history = LoanPayment::query()
            ->where('loan_id', $payment->loan_id)
            ->where('id', '!=', $payment->id)
            ->latest()
            ->get();

        return view('admin.payments.show', compact('payment', 'history'));
    }

    public function approve(ReviewPaymentRequest $request, LoanPayment $payment): RedirectResponse
    {
        $this->authorize('review', $payment);

        try {
            $this->payments->approve($payment, $request->input('admin_notes'));
        } catch (RuntimeException $exception) {
            return back()->with('error', 'This payment has already been reviewed.');
        }

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Payment approved and loan marked completed.');
    }

    public function reject(ReviewPaymentRequest $request, LoanPayment $payment): RedirectResponse
    {
        $this->authorize('review', $payment);

        $notes = $request->string('admin_notes')->toString();

        if ($notes === '') {
            return back()->withErrors(['admin_notes' => 'A rejection reason is required.']);
        }

        try {
            $this->payments->reject($payment, $notes);
        } catch (RuntimeException $exception) {
            return back()->with('error', 'This payment has already been reviewed.');
        }

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Payment rejected. The customer can see the reason.');
    }

    public function updateScreenshot(UpdatePaymentScreenshotRequest $request, LoanPayment $payment): RedirectResponse
    {
        $this->authorize('updateScreenshot', $payment);

        $oldPath = $payment->screenshot_path;

        if ($request->hasFile('screenshot')) {
            $newPath = $request->file('screenshot')->store('payment-screenshots/' . $payment->user_id, 'local');
            $payment->forceFill(['screenshot_path' => $newPath])->save();
        } else {
            $payment->forceFill(['screenshot_path' => null])->save();
        }

        if (filled($oldPath)) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', $request->hasFile('screenshot') ? 'Payment screenshot updated.' : 'Payment screenshot removed.');
    }

    public function destroy(LoanPayment $payment): RedirectResponse
    {
        $this->authorize('delete', $payment);

        $screenshotPath = $payment->screenshot_path;
        $payment->delete();
        Storage::disk('local')->delete($screenshotPath);

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Payment history and screenshot deleted.');
    }

    public function screenshot(LoanPayment $payment): StreamedResponse
    {
        $this->authorize('viewScreenshot', $payment);

        abort_unless(filled($payment->screenshot_path) && $this->payments->screenshotExists($payment), 404);

        return Storage::disk('local')->response($payment->screenshot_path);
    }
}
