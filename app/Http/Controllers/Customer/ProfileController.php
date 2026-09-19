<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('customer.profile', [
            'customer' => $request->user(),
        ]);
    }
}
