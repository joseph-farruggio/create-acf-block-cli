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

        $filesystem = app('files');
        $ignore     = ['vendor', 'node_modules'];

        $directories = $this->fetchDirectories($filesystem, $currentDirectory, $ignore, $query);

        // Convert absolute paths to relative paths
        $directories = array_map(function ($directory) use ($currentDirectory) {
            return str_replace($currentDirectory . DIRECTORY_SEPARATOR, '', $directory);
        }, $directories);

        return collect($directories);
    }



    protected function fetchDirectories($filesystem, $dir, $ignore, $query, $depth = 0)
    {
        $results = [];

        if ($depth > 3) { // Adjust depth as needed
            return $results;
        }

        foreach ($filesystem->directories($dir) as $directory) {
            $dirName = basename($directory);

            if (in_array($dirName, $ignore)) {
                continue;
            }

            // Check if the directory matches the query
            if (!$query || Str::contains($directory, $query)) {
                $results[] = $directory;
            }

            // Recursively fetch subdirectories
            $results = array_merge($results, $this->fetchDirectories($filesystem, $directory, $ignore, $query, $depth + 1));
        }

        return $results;
    }


}