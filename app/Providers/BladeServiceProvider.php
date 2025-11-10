<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Blade::if('hasPermission', function ($permission) {
            return Auth::user() &&
                (Auth::user()->is_superuser || Auth::user()->hasPermission($permission));
        });

        Blade::if('superuser', function () {
            return Auth::user() && Auth::user()->is_superuser;
        });

        Blade::component('components.ui.export-buttons', 'export-buttons');
        Blade::component('components.ui.export-message', 'export-message');
    }
}
