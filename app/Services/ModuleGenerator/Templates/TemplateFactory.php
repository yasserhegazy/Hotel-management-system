<?php

namespace App\Services\ModuleGenerator\Templates;

use Illuminate\Support\Str;

class TemplateFactory
{
    public function getTemplate(string $fileName, string $moduleName): string
    {
        return match ($fileName) {
            'config.php' => $this->getConfigTemplate($moduleName),
            'helpers.php' => $this->getHelpersTemplate($moduleName),
            'Routes/api.php' => $this->getRoutesTemplate($moduleName),
            default => ''
        };
    }

    public function getServiceProviderTemplate(string $moduleName): string
    {
        $configKey = Str::snake($moduleName);

        return <<<PHP
<?php

namespace Modules\\{$moduleName}\\Providers;

use Illuminate\Support\ServiceProvider;

class {$moduleName}ServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register bindings in the container
        // Example:
        // \$this->app->bind(YourInterface::class, YourImplementation::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load routes
        \$this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        // Load migrations
        \$this->loadMigrationsFrom(__DIR__.'/../Database/migrations');

        // Merge config
        \$this->mergeConfigFrom(
            __DIR__.'/../config.php',
            '{$configKey}'
        );

        // Load helpers
        if (file_exists(__DIR__.'/../helpers.php')) {
            require_once __DIR__.'/../helpers.php';
        }
    }
}

PHP;
    }

    private function getConfigTemplate(string $moduleName): string
    {
        return <<<PHP
<?php

return [
    // {$moduleName} module configuration
];

PHP;
    }

    private function getHelpersTemplate(string $moduleName): string
    {
        return <<<PHP
<?php

// {$moduleName} module helpers

PHP;
    }

    private function getRoutesTemplate(string $moduleName): string
    {
        $routePrefix = Str::kebab(Str::plural($moduleName));

        return <<<PHP
<?php

use Illuminate\Support\Facades\Route;

Route::prefix('{$routePrefix}')->group(function () {
    // Define your {$moduleName} routes here
    // Example:
    // Route::get('/', [YourController::class, 'index']);
});

PHP;
    }
}
