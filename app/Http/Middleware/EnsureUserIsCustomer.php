<?php

namespace App\Http\Middleware;

use App\Support\AppBrand;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isCustomer() || ! $user->isActive()) {
            AppBrand::forgetClient();
            auth()->logout();

            return redirect()->route('client.login')
                ->with('error', 'Your customer account is not available.');
        }

        return $next($request);
    }
}
