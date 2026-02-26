<?php

namespace Modules\Tenants\Providers;

use Illuminate\Support\ServiceProvider;

class TenantsServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'tenants');
        $this->mergeConfigFrom(__DIR__.'/../config.php', 'tenants');

        if (app()->environment('testing') && file_exists(__DIR__.'/../helpers.testing.php')) {
            require_once __DIR__.'/../helpers.testing.php';
        }
    }
}
