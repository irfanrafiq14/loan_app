<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FeaturedLoanController;
use App\Http\Controllers\Admin\LoanController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\SupportEmailController;
use App\Http\Controllers\Customer\AccessController;
use App\Http\Controllers\Customer\ApplyController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AccessController::class, 'showClientLogin'])->name('client.login');
Route::post('/', [AccessController::class, 'submitClientPhone'])->name('client.login.phone');
Route::get('/login', fn () => redirect()->route('client.login'));
Route::post('/login', [AccessController::class, 'submitClientPhone']);

Route::middleware('auth')->get('/featured-loans/{loan}/image', [FeaturedLoanController::class, 'image'])->name('featured-loans.image');

Route::get('/access/{token}', function (string $token) {
    $clean = strtolower(preg_replace('/[^a-z0-9]/', '', $token) ?? '');

    if (strlen($clean) >= \App\Support\AppBrand::TOKEN_MIN_LENGTH) {
        return redirect()->route('client.login', ['t' => $clean]);
    }

    return redirect()->route('client.login');
});
Route::get('/verify-otp', [AccessController::class, 'showOtp'])->name('verify-otp.show');
Route::post('/verify-otp', [AccessController::class, 'verify'])->name('verify-otp.verify');
Route::post('/verify-otp/autofill', [AccessController::class, 'autofill'])->name('verify-otp.autofill');
Route::post('/verify-otp/resend', [AccessController::class, 'resend'])->name('verify-otp.resend');

Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/home', HomeController::class)->name('home');
    Route::get('/orders', OrderController::class)->name('orders');
    Route::get('/profile', ProfileController::class)->name('profile');
    Route::get('/loans/{loan}/apply', [ApplyController::class, 'show'])->name('loans.apply');
    Route::post('/loans/{loan}/apply', [ApplyController::class, 'store'])->name('loans.apply.store');
    Route::get('/loans/{loan}/pay', [PaymentController::class, 'create'])->name('loans.pay');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('/logout', [AccessController::class, 'logout'])->name('logout');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/', [AdminAuthController::class, 'store'])->name('login.store');
    Route::get('/login', fn () => redirect()->route('admin.login'));
    Route::post('/login', [AdminAuthController::class, 'store']);

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::resource('customers', CustomerController::class)->except(['destroy']);
        Route::post('/customers/{customer}/deactivate', [CustomerController::class, 'deactivate'])->name('customers.deactivate');

        Route::resource('loans', LoanController::class);
        Route::resource('featured-loans', FeaturedLoanController::class)
            ->parameters(['featured-loans' => 'loan'])
            ->except(['show']);

        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{payment}', [AdminPaymentController::class, 'show'])->name('payments.show');
        Route::post('/payments/{payment}/approve', [AdminPaymentController::class, 'approve'])->name('payments.approve');
        Route::post('/payments/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('payments.reject');
        Route::get('/payments/{payment}/screenshot', [AdminPaymentController::class, 'screenshot'])->name('payments.screenshot');

        Route::get('/payment-link', [PaymentMethodController::class, 'edit'])->name('payment-link.edit');
        Route::put('/payment-link', [PaymentMethodController::class, 'update'])->name('payment-link.update');
        Route::get('/support-email', [SupportEmailController::class, 'edit'])->name('support-email.edit');
        Route::put('/support-email', [SupportEmailController::class, 'update'])->name('support-email.update');
        Route::get('/payment-methods', fn () => redirect()->route('admin.payment-link.edit'));
        Route::get('/payment-methods/create', fn () => redirect()->route('admin.payment-link.edit'));
        Route::get('/payment-methods/{payment_method}/edit', fn () => redirect()->route('admin.payment-link.edit'));
    });
});
