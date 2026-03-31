<?php

namespace Modules\Staff\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class StaffServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Tenant migrations must only run via `php artisan tenants:migrate`.
        // Running them here would create tenant_users in the central (saas_central) DB.
        if (app()->runningUnitTests()) {
            $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');
        }

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'staff');

        Route::middleware('api')
            ->prefix('api/v1')
            ->group(__DIR__.'/../Routes/api.php');

        $this->mergeConfigFrom(__DIR__.'/../config.php', 'staff');

        if (file_exists(__DIR__.'/../helpers.php')) {
            require_once __DIR__.'/../helpers.php';
        }

        if (app()->environment('testing') && file_exists(__DIR__.'/../helpers.testing.php')) {
            require_once __DIR__.'/../helpers.testing.php';
        }
    }
}
