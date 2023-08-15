<?php
namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DirectoryService
{

    public function getDirectories(string $dir, string $query = ''): Collection
    {
        $ignore      = ['vendor', 'node_modules'];
        $directories = collect(Storage::allDirectories())
            ->filter(function ($directory) use ($ignore, $query) {
                foreach ($ignore as $ignoredDirectory) {
                    if (Str::startsWith($directory, $ignoredDirectory)) {
                        return false;
                    }
                }

                if ($query) {
                    return Str::contains($directory, $query);
                }
            });
        return $directories;
    }
}