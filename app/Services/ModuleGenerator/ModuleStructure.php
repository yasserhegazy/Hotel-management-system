<?php

namespace App\Services\ModuleGenerator;

class ModuleStructure
{
    private array $directories = [
        'Database/Factories',
        'Database/migrations',
        'Database/Seeders',
        'Domain/DTOs',
        'Domain/Models',
        'Domain/Repositories',
        'Domain/Services',
        'Http/Controllers/Api/v1',
        'Http/Middleware',
        'Http/Requests',
        'Http/Resources',
        'Providers',
        'Routes',
        'Tests/Feature',
        'Tests/Unit',
    ];

    private array $files = [
        'config.php',
        'helpers.php',
        'Routes/api.php',
    ];

    public function getDirectories(): array
    {
        return $this->directories;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function getServiceProviderPath(string $moduleName): string
    {
        return "Providers/{$moduleName}ServiceProvider.php";
    }
}
