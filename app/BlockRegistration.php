<?php

namespace App;

use Illuminate\Support\Facades\File;
use App\Services\ConfigService;
use App\Services\PathService;
use App\Timer;

class BlockRegistration
{
    public $config;
    public $block;
    public $registrationFilePath;
    public $pathService;
    public $stubDir;
    public $configService;


    public function __construct(PathService $pathService, ConfigService $configService)
    {
        $this->pathService   = $pathService;
        $this->configService = $configService;
        $this->stubDir       = $this->pathService->base_path('resources/stubs');
        $this->config        = $this->configService->config;
    }

    public function handle($config, $block)
    {
        $this->config = $config;
        $this->block  = $block;

        $this->pathService   = app(PathService::class);
        $this->configService = app(ConfigService::class);

        if (!$this->configService->config['createRegistrationFile']) {
            return;
        }

        $this->createRegistrationFile();
        $this->addBlock();
    }

    public function createRegistrationFile()
    {

        $this->registrationFilePath = $this->configService->get('registrationFileDir') . '/register-acf-blocks-cli.php';
        if (!File::exists($this->registrationFilePath)) {
            $registrationFileContents = File::get($this->stubDir . '/register-acf-blocks-cli.php.stub');

            if ($this->configService->get('useAcfFieldBuilder')) {
                $registrationFileContents = str_replace('{{LoadFields}}', File::get($this->stubDir . '/fields-builder.php.stub'), $registrationFileContents);
            } else {
                $registrationFileContents = str_replace('{{LoadFields}}', '', $registrationFileContents);
            }

            $registrationFileContents = str_replace('{{BlockPath}}', $this->pathService->getNakedPath($this->configService->get('blocksDirPath')), $registrationFileContents);

            File::put($this->registrationFilePath, $registrationFileContents);
        }
    }

    public function addBlock()
    {
        $contents = File::get($this->registrationFilePath);
        if (preg_match('/\$blocks\s*=\s*array\s*\(([^;]*)\)/', $contents, $matches)) {
            $arrayContents = $matches[1];

            // Transform the matched string into a real PHP array
            eval("\$parsedArray = array($arrayContents);");

            // Step 3: Insert a new item in the array.
            $newItem       = $this->block['name']; // Replace with the desired string
            $parsedArray[] = $newItem;

            // Step 4: Sort the array items alphabetically
            sort($parsedArray);

            // Convert the PHP array back to string format, with each item on its own line
            $sortedArrayContents = "array(\n    " . implode(",\n    ", array_map(function ($item) {
                return "'$item'";
            }, $parsedArray)) . "\n)";

            // Replace the original array in the file content with the new sorted array
            $contents = str_replace($matches[0], "\$blocks = $sortedArrayContents", $contents);


            // Step 5: Save the array back to the file
            File::put($this->registrationFilePath, $contents);
        }
    }
}