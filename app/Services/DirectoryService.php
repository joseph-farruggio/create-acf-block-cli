<?php
namespace App\Services;

use Illuminate\Support\Collection;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class DirectoryService
{
    public function getDirectories(string $dir, string $query = ''): Collection
    {
        $ignore = ['./vendor', 'node_modules']; // Directories you want to ignore

        $dirIterator = new RecursiveDirectoryIterator($dir);
        $iterator    = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

        $ignore = collect($ignore)->map(function ($path) use ($dir) {
            return realpath($dir . DIRECTORY_SEPARATOR . $path);
        })->filter();

        $cwd = getcwd(); // Get the current working directory

        return collect(iterator_to_array($iterator))->filter(function ($fileInfo) use ($dir, $query, $ignore) {
            $currentPath = $fileInfo->getRealPath();

            if (
                $ignore->contains(function ($ignorePath) use ($currentPath) {
                    return strpos($currentPath, $ignorePath) === 0;
                })
            ) {
                return false;
            }

            if ($fileInfo->isDir() && stripos($fileInfo->getFilename(), $query) !== false) {
                return true;
            }

            return false;
        })->map(function ($fileInfo) use ($cwd) {
            $relativePath = str_replace($cwd, '', $fileInfo->getRealPath());

            // If the relativePath still begins with a directory separator, remove it.
            if (strpos($relativePath, DIRECTORY_SEPARATOR) === 0) {
                $relativePath = ltrim($relativePath, DIRECTORY_SEPARATOR);
            }

            return $relativePath;
        });
    }
}