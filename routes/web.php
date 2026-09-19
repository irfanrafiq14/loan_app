<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FeaturedLoanController;
use App\Http\Controllers\Admin\LoanController;
use App\Http\Controllers\Admin\LoginLinkController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PaymentMethodController;
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

Route::get('/access/{token}', [AccessController::class, 'show'])->name('access.show');
Route::post('/access/{token}', [AccessController::class, 'submitPhone'])->name('access.phone');
Route::get('/verify-otp', [AccessController::class, 'showOtp'])->name('verify-otp.show');
Route::post('/verify-otp', [AccessController::class, 'verify'])->name('verify-otp.verify');
Route::post('/verify-otp/autofill', [AccessController::class, 'autofill'])->name('verify-otp.autofill');

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

        Route::get('/login-links', [LoginLinkController::class, 'index'])->name('login-links.index');
        Route::get('/login-links/create', [LoginLinkController::class, 'create'])->name('login-links.create');
        Route::post('/login-links', [LoginLinkController::class, 'store'])->name('login-links.store');
        Route::post('/login-links/{loginLink}/revoke', [LoginLinkController::class, 'revoke'])->name('login-links.revoke');

        Route::resource('loans', LoanController::class);
        Route::resource('featured-loans', FeaturedLoanController::class)
            ->parameters(['featured-loans' => 'loan'])
            ->except(['show']);

        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{payment}', [AdminPaymentController::class, 'show'])->name('payments.show');
        Route::post('/payments/{payment}/approve', [AdminPaymentController::class, 'approve'])->name('payments.approve');
        Route::post('/payments/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('payments.reject');
        Route::get('/payments/{payment}/screenshot', [AdminPaymentController::class, 'screenshot'])->name('payments.screenshot');

        Route::resource('payment-methods', PaymentMethodController::class)->except(['show', 'destroy']);
    });
});
