<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsCustomer;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'customer' => EnsureUserIsCustomer::class,
        ]);

        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            return $request->is('admin') || $request->is('admin/*')
                ? route('admin.login')
                : route('client.login');
        });
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();

            return $user?->isAdmin()
                ? route('admin.dashboard')
                : route('home');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (HttpException $exception, Request $request) {
            if ($exception->getStatusCode() === 419 && ($request->is('admin') || $request->is('admin/*'))) {
                return redirect()->route('admin.login')->with('error', 'Your session expired. Please sign in again.');
            }
        });
    })->create();
