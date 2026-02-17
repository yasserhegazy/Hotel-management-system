<?php

namespace App\Services\ModuleGenerator;

use App\Services\ModuleGenerator\Contracts\FileSystemInterface;

class ServiceProviderRegistrar
{
    private const PROVIDERS_FILE = 'bootstrap/providers.php';

    public function __construct(
        private FileSystemInterface $fileSystem
    ) {}

    public function register(string $basePath, string $moduleName): bool
    {
        $providersFile = "{$basePath}/" . self::PROVIDERS_FILE;

        if (!$this->fileSystem->exists($providersFile)) {
            throw new \RuntimeException('Could not find bootstrap/providers.php');
        }

        $providerClass = "Modules\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider::class";
        $content = $this->fileSystem->get($providersFile);

        // Check if provider is already registered
        if (str_contains($content, $providerClass)) {
            return false; // Already registered
        }

        // Add the provider before the closing bracket
        $content = preg_replace(
            '/\];(\s*)$/',
            "    {$providerClass},\n];$1",
            $content
        );

        $this->fileSystem->put($providersFile, $content);

        return true;
    }
}
