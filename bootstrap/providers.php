<?php

$providers = [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\BladeServiceProvider::class,
    App\Providers\EmailServiceProvider::class,
    App\Providers\NfeServiceProvider::class,
    Intervention\Image\ImageServiceProvider::class,
    Spatie\Permission\PermissionServiceProvider::class,
];

// Conditionally register Telescope providers to avoid memory issues on heavy pages
if (env('TELESCOPE_ENABLED', false)) {
    $providers[] = App\Providers\TelescopeServiceProvider::class;
    $providers[] = Laravel\Telescope\TelescopeServiceProvider::class;
}

return $providers;
