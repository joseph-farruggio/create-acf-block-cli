<?php

namespace App;

use Illuminate\Support\Facades\File;

use function Laravel\Prompts\text;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\note;

use App\Services\DirectoryService;
use App\Services\PathService;

class ConfigPrompts
{
    protected $directoryService;
    protected $pathService;
    public $config;
    public $registrationFilePath;
    public $blockDir;
    public $stubDir;

    public function __construct(DirectoryService $directoryService, PathService $pathService)
    {
        $this->directoryService = $directoryService;
        $this->pathService      = $pathService;
        $this->stubDir          = $this->pathService->base_path('stubs');
    }

    public function handle($app)
    {
        /**
         * Config Prompts
         * 1. Check if the config file exists
         * 2. If it does, serve the config prompts
         * 3. Save the config JSON file
         */
        if (!File::exists('./create-acf-block.config.json')) {
            $config = [];

            // Block Namespace
            $config['blockNamespace'] = text('Block Namespace:', placeholder: "acf", required: true);

            // Use block.json?
            $config['useBlockJSON'] = confirm('Use block.json?');

            // Create a block registration file?
            $config['createRegistrationFile'] = confirm(
                label: 'Create a block registration file?',
                yes: 'Yes',
                no: 'No, I\'ll register blocks myself',
            );

            // If we're creating a registration file, where should it be created?
            if ($config['createRegistrationFile']) {
                $config['registrationFileDir'] = search(
                    'Where should your block registration file be created?',
                    fn(string $value) => strlen($value) > 0
                    ? $this->directoryService->getDirectories(getcwd(), $value)->toArray()
                    : []
                );
                $config['registrationFileDir'] = $this->pathService->getRelPath($config['registrationFileDir']);

                $this->registrationFilePath = $config['registrationFileDir'] . '/register-acf-blocks.php';
            }

            // Where should blocks be stored?
            $config['blocksDirPath'] = search(
                'Search for your blocks directory',
                fn(string $value) => strlen($value) > 0
                ? $this->directoryService->getDirectories(getcwd(), $value)->toArray()
                : []
            );
            $config['blocksDirPath'] = $this->pathService->getRelPath($config['blocksDirPath']);

            if ($config['createRegistrationFile']) {
                // Create the registration file
                if (!File::exists($this->registrationFilePath)) {
                    $contents = "<?php\n\n";
                    $contents .= "// ACF Block Registration\n";
                    $contents .= "\$blocks=array();\n\n";
                    $contents .= "foreach (\$blocks as \$block) {\n";
                    $contents .= "    register_block_type( get_template_directory() . '/" . $this->pathService->getNakedPath($config['blocksDirPath']) . "/' . \$block );\n";
                    $contents .= "}\n";
                    File::put($this->registrationFilePath, $contents);
                }
                $app->info("Note: \nRegistration file created at: {$config['registrationFileDir']}/register-acf-blocks.php \nMake sure to include this file in your functions.php ");
            }

            // Create block specific CSS and JS files?
            $config['blockAssets'] = confirm('Create block specific CSS and JS files?');

            // If we're creating block specific CSS and JS files, where should they be stored?
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
                    $config['blockCssDirPath'] = $this->pathService->getRelPath($config['blockCssDirPath']);
                }

                if (!$config['groupBlockAssets']) {
                    $config['blockJsDirPath'] = search(
                        'Search for your block JS directory',
                        fn(string $value) => strlen($value) > 0
                        ? $this->directoryService->getDirectories(getcwd(), $value)->toArray()
                        : []
                    );
                    $config['blockJsDirPath'] = $this->pathService->getRelPath($config['blockJsDirPath']);
                }
            }

            // Save the config file
            File::put('./create-acf-block.config.json', json_encode($config, JSON_PRETTY_PRINT));
        }
    }

    public function getConfig()
    {
        return json_decode(File::get('./create-acf-block.config.json'), true);
    }
}