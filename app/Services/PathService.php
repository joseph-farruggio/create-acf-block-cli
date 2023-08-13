<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use App\Services\PlatformService as Platform;

class PathService
{
    public static function base_path(string $path = ''): string
    {
        $code_path = "vendor/joeyfarruggio/create-acf-block/{$path}";

        return Platform::normalizePath(match (true) {
            self::dogfooding() => base_path($path),
            self::isRunningGlobally() => Platform::getGlobalComposerHome() . '/' . $code_path,
            default => Platform::cwd($code_path),
        });
    }

    public static function bin_path(): string
    {
        return Platform::normalizePath(match (true) {
            self::dogfooding() => Platform::cwd('create-acf-block'),
            self::isRunningGlobally() => Platform::getGlobalComposerBinDir() . '/create-acf-block',
            default => Platform::cwd('vendor/bin/create-acf-block'),
        });
    }

    public static function dogfooding(): bool
    {
        return Platform::cwd() === Platform::normalizePath(base_path());
    }

    public static function isInstalledGlobally(): bool
    {
        return File::exists(Platform::getGlobalComposerBinDir() . '/create-acf-block');
    }

    public static function isRunningGlobally(): bool
    {
        return str_starts_with(base_path(), 'phar://' . Platform::getGlobalComposerHome());
    }

    public static function readConfig(string $key): string|array|null
    {
        $cfg = FileJson::make(Platform::cwd('create-acf-block.json'))->read();

        return data_get($cfg, $key);
    }
}