<?php

namespace Modules\Staff\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class StaffServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');

        Route::middleware('api')->group(__DIR__.'/../Routes/api.php');

        $this->mergeConfigFrom(__DIR__.'/../config.php', 'staff');

        if (file_exists(__DIR__.'/../helpers.php')) {
            require_once __DIR__.'/../helpers.php';
        }

        if (app()->environment('testing') && file_exists(__DIR__.'/../helpers.testing.php')) {
            require_once __DIR__.'/../helpers.testing.php';
        }
    }
}
