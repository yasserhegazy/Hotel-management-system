<?php

namespace App\Services\ModuleGenerator;

use App\Services\ModuleGenerator\Contracts\FileSystemInterface;
use App\Services\ModuleGenerator\Templates\TemplateFactory;

class FileGenerator
{
    public function __construct(
        private FileSystemInterface $fileSystem,
        private TemplateFactory $templateFactory
    ) {}

    public function generate(string $basePath, string $moduleName, array $files): void
    {
        foreach ($files as $file) {
            $filePath = "{$basePath}/{$file}";
            $content = $this->templateFactory->getTemplate($file, $moduleName);
            $this->fileSystem->put($filePath, $content);
        }
    }

    public function generateServiceProvider(string $basePath, string $moduleName, string $relativePath): void
    {
        $filePath = "{$basePath}/{$relativePath}";
        $content = $this->templateFactory->getServiceProviderTemplate($moduleName);
        $this->fileSystem->put($filePath, $content);
    }
}
