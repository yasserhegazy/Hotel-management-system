<?php

namespace App\Services\ModuleGenerator;

use App\Services\ModuleGenerator\Contracts\FileSystemInterface;

class ModuleGenerator
{
    public function __construct(
        private FileSystemInterface $fileSystem,
        private ModuleStructure $structure,
        private DirectoryCreator $directoryCreator,
        private FileGenerator $fileGenerator,
        private ServiceProviderRegistrar $providerRegistrar
    ) {}

    public function generate(string $basePath, string $moduleName): void
    {
        $modulePath = "{$basePath}/modules/{$moduleName}";

        if ($this->fileSystem->exists($modulePath)) {
            throw new \RuntimeException("Module {$moduleName} already exists!");
        }

        // Create directories
        $this->directoryCreator->create(
            $modulePath,
            $this->structure->getDirectories()
        );

        // Generate base files
        $this->fileGenerator->generate(
            $modulePath,
            $moduleName,
            $this->structure->getFiles()
        );

        // Generate service provider
        $this->fileGenerator->generateServiceProvider(
            $modulePath,
            $moduleName,
            $this->structure->getServiceProviderPath($moduleName)
        );

        // Register service provider
        $this->providerRegistrar->register($basePath, $moduleName);
    }
}
