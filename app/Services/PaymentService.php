<?php

namespace App\Services;

use App\Enums\LoanStatus;
use App\Enums\PaymentStatus;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentService
{
    public function submit(User $user, Loan $loan, array $data, UploadedFile $screenshot): LoanPayment
    {
        if ($loan->user_id !== $user->id) {
            throw new RuntimeException('unauthorized');
        }

        if (! $loan->canAcceptPayment()) {
            throw new RuntimeException('not_payable');
        }

        $extension = strtolower($screenshot->getClientOriginalExtension());
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (! in_array($extension, $allowed, true)) {
            throw new RuntimeException('invalid_file');
        }

        $filename = Str::uuid()->toString().'.'.$extension;
        $path = $screenshot->storeAs(
            'payment-screenshots/'.$user->id,
            $filename,
            'local'
        );

        if (! $path) {
            throw new RuntimeException('upload_failed');
        }

        return LoanPayment::query()->create([
            'loan_id' => $loan->id,
            'user_id' => $user->id,
            'payment_method_id' => $data['payment_method_id'],
            'transaction_id' => $data['transaction_id'],
            'screenshot_path' => $path,
            'status' => PaymentStatus::Pending,
            'submitted_at' => now(),
        ]);
    }

    public function approve(LoanPayment $payment, ?string $notes = null): void
    {
        DB::transaction(function () use ($payment, $notes) {
            $payment = LoanPayment::query()->lockForUpdate()->findOrFail($payment->id);

            if (! $payment->isPending()) {
                throw new RuntimeException('already_reviewed');
            }

            $payment->forceFill([
                'status' => PaymentStatus::Completed,
                'admin_notes' => $notes,
                'reviewed_at' => now(),
            ])->save();

            $payment->loan()->update([
                'status' => LoanStatus::Completed,
            ]);
        });
    }

    public function reject(LoanPayment $payment, string $notes): void
    {
        DB::transaction(function () use ($payment, $notes) {
            $payment = LoanPayment::query()->lockForUpdate()->findOrFail($payment->id);

            if (! $payment->isPending()) {
                throw new RuntimeException('already_reviewed');
            }

            $payment->forceFill([
                'status' => PaymentStatus::Rejected,
                'admin_notes' => $notes,
                'reviewed_at' => now(),
            ])->save();
        });
    }

    public function screenshotExists(LoanPayment $payment): bool
    {
        return Storage::disk('local')->exists($payment->screenshot_path);
    }
}
