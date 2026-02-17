<?php

namespace App\Services\ModuleGenerator;

use App\Services\ModuleGenerator\Contracts\FileSystemInterface;

class DirectoryCreator
{
    public function __construct(
        private FileSystemInterface $fileSystem
    ) {}

    public function create(string $basePath, array $directories): void
    {
        foreach ($directories as $directory) {
            $path = "{$basePath}/{$directory}";
            $this->fileSystem->makeDirectory($path, 0755, true);
        }
    }
}
