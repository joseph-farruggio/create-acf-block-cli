<?php
namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemManager;

class DirectoryService
{
    public function getDirectories(string $dir, string $query = ''): Collection
    {
        $currentDirectory = getcwd();
        if (!is_dir($currentDirectory) || !is_readable($currentDirectory)) {
            throw new \Exception("Invalid or unreadable directory: {$currentDirectory}");
        }

        // Create a filesystem instance
        $filesystemManager = new FilesystemManager(app());
        $storage           = $filesystemManager->createLocalDriver(['root' => $currentDirectory]);

        $ignore = ['vendor', 'node_modules', '.git'];

        $directories = collect($storage->allFiles()) // Start with all files
            ->filter(function ($path) {
                // Only keep paths that are not symbolic links
                return !is_link($path);
            })
            ->flatMap(function ($path) {
                // For each file, get its directory path using PHP's native function
                return [dirname($path)];
            })
            ->unique() // Only keep unique directory paths
            ->filter(function ($directory) use ($ignore, $query) {
                foreach ($ignore as $ignoredDirectory) {
                    if (Str::startsWith($directory, $ignoredDirectory)) {
                        return false;
                    }
                }

                if ($query) {
                    return Str::contains($directory, $query);
                }

                return true;
            });

        return $directories;
    }


}