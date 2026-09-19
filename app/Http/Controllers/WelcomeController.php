<?php

namespace App\Http\Controllers;

use App\Support\AppBrand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('home');
        }

        if (AppBrand::resumeUser()) {
            return redirect()->route('client.login');
        }

        if (AppBrand::hasClientBrand()) {
            return view('customer.locked');
        }

        return view('welcome');
    }
}
