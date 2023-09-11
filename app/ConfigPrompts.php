<?php

namespace App;

use App\Services\ConfigService;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\text;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\note;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

use App\Services\DirectoryService;
use App\Services\PathService;
use App\Timer;

class ConfigPrompts
{
    public $config;
    public $configService;
    protected $directoryService;
    protected $pathService;
    public $registrationFilePath;
    public $stubDir;

    public function __construct(DirectoryService $directoryService, PathService $pathService, ConfigService $configService)
    {
        $this->directoryService = $directoryService;
        $this->pathService      = $pathService;
        $this->stubDir          = $this->pathService->base_path('resources/stubs');

        $this->configService = $configService;
        $this->stubDir       = $this->pathService->base_path('resources/stubs');
        $this->config        = $this->configService->config;
    }

    public function handle($message = "First time setup:")
    {
        intro($message);
        $config = [];

        $config['blockNamespace'] = text(
            'Block Namespace:',
            placeholder: "acf",
            required: true,
            validate: fn(string $value) => match (true) {
                strlen($value) < 3 => 'The name must be at least 3 characters.',
                strlen($value) > 255 => 'The name must not exceed 255 characters.',
                default => null
            }
        );

        $config['createRegistrationFile'] = confirm(
            label: 'Automate block registration?',
            yes: 'Yes',
            no: 'No, I\'ll register each block myself',
        );

        if ($config['createRegistrationFile']) {
            $config['registrationFileDir'] = search(
                'Where should the global block registration file be placed?',
                fn(string $value) => strlen($value) > 0
                ? $this->directoryService->getDirectories(getcwd(), $value)->toArray()
                : []
            );
            // $config['registrationFileDir'] = $this->pathService->getNakedPath($config['registrationFileDir']);

            $this->registrationFilePath = $config['registrationFileDir'] . '/register-acf-blocks-cli.php';
        }

        $config['blocksDirPath'] = search(
            'Where should new blocks be created?',
            fn(string $value) => strlen($value) > 0
            ? $this->directoryService->getDirectories(getcwd(), $value)->toArray()
            : []
        );

        $config['blocksDirPath'] = $this->pathService->getNakedPath($config['blocksDirPath']);

        if ($config['createRegistrationFile']) {
            // Create the registration file
            $this->registrationFilePath = $config['registrationFileDir'] . '/register-acf-blocks-cli.php';
            if (!File::exists($this->registrationFilePath)) {
                $registrationFileContents = File::get($this->stubDir . '/register-acf-blocks-cli.php.stub');
                $registrationFileContents = str_replace('{{BlockPath}}', $this->pathService->getNakedPath($config['blocksDirPath']), $registrationFileContents);
                File::put($this->registrationFilePath, $registrationFileContents);
            }
            note("Note: \nRegistration file created at: {$config['registrationFileDir']}/register-acf-blocks-cli.php \nMake sure to include this file in your functions.php ", type: 'info');
        }

        $config['blockAssets'] = confirm('Create block specific CSS and JS files?');



        if ($config['blockAssets']) {
            $config['groupBlockAssets'] = Select(
                label: 'Where should block assets be stored?',
                options: [true => 'With the block render template (eg: ./blocks/my-block/my-block.css)', false => 'Let me specify (eg: ./src/css/my-block.css)'], default
                : 'grouped'
            );

            if (!$config['groupBlockAssets']) {
                $config['blockCssDirPath'] = search(
                    'Search for your block CSS directory',
                    fn(string $value) => strlen($value) > 0
                    ? $this->directoryService->getDirectories(getcwd(), $value)->toArray()
                    : []
                );
                $config['blockCssDirPath'] = $this->pathService->getNakedPath($config['blockCssDirPath']);
            }

            if (!$config['groupBlockAssets']) {
                $config['blockJsDirPath'] = search(
                    'Search for your block JS directory',
                    fn(string $value) => strlen($value) > 0
                    ? $this->directoryService->getDirectories(getcwd(), $value)->toArray()
                    : []
                );
                $config['blockJsDirPath'] = $this->pathService->getNakedPath($config['blockJsDirPath']);
            }
        }

        $config['blockSlugify'] = confirm(
            label: 'Slugify block names based on the block title?',
            yes: 'Yes',
            no: 'No, I\'ll define block names myself',
        );


        outro("Configuration complete");
        // Save the config file
        File::put('./create-acf-block.config.json', json_encode($config, JSON_PRETTY_PRINT));
    }
}