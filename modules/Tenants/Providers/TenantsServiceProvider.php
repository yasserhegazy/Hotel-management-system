<?php

namespace Modules\Tenants\Providers;

use Illuminate\Support\ServiceProvider;

class TenantsServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');
        $this->mergeConfigFrom(__DIR__.'/../config.php', 'tenants');

        if (file_exists(__DIR__.'/../helpers.php')) {
            require_once __DIR__.'/../helpers.php';
        }
    }
}
