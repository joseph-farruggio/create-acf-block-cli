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
    public $registrationFilePath;
    public $stubDir;

    public function __construct(DirectoryService $directoryService, PathService $pathService)
    {
        $this->directoryService = $directoryService;
        $this->pathService      = $pathService;
        $this->stubDir          = $this->pathService->base_path('resources/stubs');
    }

    public function handle($reset = false)
    {
        if (File::exists('./acf-block-cli.config.json') && !$reset) {
            return json_decode(File::get('./acf-block-cli.config.json'), true);
        }

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
            label: 'Create a block registration file?',
            yes: 'Yes',
            no: 'No, I\'ll register blocks myself',
        );

        if ($config['createRegistrationFile']) {
            $config['registrationFileDir'] = search(
                'Where should your block registration file be created?',
                fn(string $value) => strlen($value) > 0
                ? $this->directoryService->getDirectories(getcwd(), $value)->toArray()
                : []
            );
            // $config['registrationFileDir'] = $this->pathService->getNakedPath($config['registrationFileDir']);

            $this->registrationFilePath = $config['registrationFileDir'] . '/register-acf-blocks.php';
        }

        $config['blocksDirPath'] = search(
            'Search for your blocks directory',
            fn(string $value) => strlen($value) > 0
            ? $this->directoryService->getDirectories(getcwd(), $value)->toArray()
            : []
        );

        $config['blocksDirPath'] = $this->pathService->getNakedPath($config['blocksDirPath']);

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
            note("Note: \nRegistration file created at: {$config['registrationFileDir']}/register-acf-blocks.php \nMake sure to include this file in your functions.php ", type: 'info');

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

        // Save the config file
        File::put('./acf-block-cli.config.json', json_encode($config, JSON_PRETTY_PRINT));
        return json_decode(File::get('./acf-block-cli.config.json'), true);
    }
}