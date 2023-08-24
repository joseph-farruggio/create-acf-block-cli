<?php
namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;


class ConfigService
{
    public $config;

    public function __construct()
    {
        try {
            $this->config = json_decode(File::get('./acf-block-cli.config.json'), true);
        } catch (FileNotFoundException $e) {
            $this->config = []; // Default empty configuration
        }
    }

    public function configIsSet()
    {
        return !empty($this->config);
    }


    public function get($key)
    {
        return $this->config[$key];
    }
}