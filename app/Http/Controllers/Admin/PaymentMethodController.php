<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class PaymentMethodController extends Controller
{
    public function edit(): RedirectResponse
    {
        return redirect()->route('admin.customers.index');
    }

    public function update(): RedirectResponse
    {
        return redirect()->route('admin.customers.index');
    }
}
