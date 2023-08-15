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

        if (!is_dir($currentDirectory)) {
            throw new \Exception("Invalid directory: {$currentDirectory}");
        }

        if (!is_readable($currentDirectory)) {
            throw new \Exception("Cannot read from directory: {$currentDirectory}. Please ensure you have appropriate permissions.");
        }

        // Create a filesystem instance
        $filesystemManager = new FilesystemManager(app());
        $storage           = $filesystemManager->createLocalDriver(['root' => $currentDirectory]);

        $ignore      = ['vendor', 'node_modules'];
        $directories = collect($storage->allDirectories())
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