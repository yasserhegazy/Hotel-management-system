<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register bindings in the container
        // Example:
        // $this->app->bind(YourInterface::class, YourImplementation::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');

        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config.php',
            'auth'
        );

        // Load helpers
        if (file_exists(__DIR__.'/../helpers.php')) {
            require_once __DIR__.'/../helpers.php';
        }
    }
}
