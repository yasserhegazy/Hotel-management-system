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
    public function register(): void {}

    public function boot(): void
    {
        \$this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        \$this->loadMigrationsFrom(__DIR__.'/../Database/migrations');
        \$this->mergeConfigFrom(__DIR__.'/../config.php', '{$configKey}');

        if (file_exists(__DIR__.'/../helpers.php')) {
            require_once __DIR__.'/../helpers.php';
        }
    }
}

PHP;
    }

    private function getConfigTemplate(string $moduleName): string
    {
        return <<<'PHP'
<?php

return [];

PHP;
    }

    private function getHelpersTemplate(string $moduleName): string
    {
        return <<<'PHP'
<?php

PHP;
    }

    private function getRoutesTemplate(string $moduleName): string
    {
        $routePrefix = Str::kebab(Str::plural($moduleName));

        return <<<PHP
<?php

use Illuminate\Support\Facades\Route;

Route::prefix('{$routePrefix}')->group(function () {
    //
});

PHP;
    }
}
