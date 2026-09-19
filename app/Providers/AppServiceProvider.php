<?php

namespace App\Providers;

use App\Support\AppBrand;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer([
            'components.layouts.customer',
            'components.layouts.guest',
            'customer.*',
        ], function ($view) {
            $view->with('appName', AppBrand::name());
        });
    }
}
