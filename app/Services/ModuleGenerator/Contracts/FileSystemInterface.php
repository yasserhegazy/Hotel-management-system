<?php

namespace App\Services\ModuleGenerator\Contracts;

interface FileSystemInterface
{
    public function exists(string $path): bool;

    public function makeDirectory(string $path, int $mode = 0755, bool $recursive = false): bool;

    public function put(string $path, string $contents): bool|int;

    public function get(string $path): string;
}
