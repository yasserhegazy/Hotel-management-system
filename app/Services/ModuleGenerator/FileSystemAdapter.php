<?php

namespace App\Services\ModuleGenerator;

use App\Services\ModuleGenerator\Contracts\FileSystemInterface;
use Illuminate\Support\Facades\File;

class FileSystemAdapter implements FileSystemInterface
{
    public function exists(string $path): bool
    {
        return File::exists($path);
    }

    public function makeDirectory(string $path, int $mode = 0755, bool $recursive = false): bool
    {
        return File::makeDirectory($path, $mode, $recursive);
    }

    public function put(string $path, string $contents): bool|int
    {
        return File::put($path, $contents);
    }

    public function get(string $path): string
    {
        return File::get($path);
    }
}
