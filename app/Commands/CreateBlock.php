<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use App\Services\DirectoryService;
use App\Services\PathService;

use function Laravel\Prompts\text;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\note;

class CreateBlock extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'app:create-block';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $directoryService;
    protected $pathService;
    public $config;
    public $registrationFilePath;
    public $blockDir;
    public $stubDir;

    public function __construct(DirectoryService $directoryService, PathService $pathService)
    {
        parent::__construct();

        $this->directoryService = $directoryService;
        $this->pathService      = $pathService;
        $this->stubDir          = $this->pathService->base_path('stubs');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /**
         * Config Prompts
         * 1. Check if the config file exists
         * 2. If it does, serve the config prompts
         * 3. Save the config JSON file
         */
        if (!File::exists('./create-acf-block.config.json')) {
            $config = [];

            $config['blockNamespace'] = text('Block Namespace:', placeholder: "acf", required: true);

            $config['useBlockJSON'] = confirm('Use block.json?');

            $config['registrationFileDir'] = search(
                'Where should your block registration file be created?',
                fn(string $value) => strlen($value) > 0
                ? $this->directoryService->getDirectories(getcwd(), $value)->toArray()
                : []
            );
            $config['registrationFileDir'] = $this->getRelPath($config['registrationFileDir']);

            $this->registrationFilePath = $config['registrationFileDir'] . '/register-acf-blocks.php';

            $config['blocksDirPath'] = search(
                'Search for your blocks directory',
                fn(string $value) => strlen($value) > 0
                ? $this->directoryService->getDirectories(getcwd(), $value)->toArray()
                : []
            );
            $config['blocksDirPath'] = $this->getRelPath($config['blocksDirPath']);

            if (!File::exists($this->registrationFilePath)) {
                $contents = "<?php\n\n";
                $contents .= "// ACF Block Registration\n";
                $contents .= "\$blocks=array();\n\n";
                $contents .= "foreach (\$blocks as \$block) {\n";
                $contents .= "    register_block_type( get_template_directory() . '/" . $this->getNakedPath($config['blocksDirPath']) . "/' . \$block );\n";
                $contents .= "}\n";
                File::put($this->registrationFilePath, $contents);
            }
            $this->info("Note: \nRegistration file created at: {$config['registrationFileDir']}/register-acf-blocks.php \nMake sure to include this file in your functions.php ");

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
                    $config['blockCssDirPath'] = $this->getRelPath($config['blockCssDirPath']);
                }

                if (!$config['groupBlockAssets']) {
                    $config['blockJsDirPath'] = search(
                        'Search for your block JS directory',
                        fn(string $value) => strlen($value) > 0
                        ? $this->directoryService->getDirectories(getcwd(), $value)->toArray()
                        : []
                    );
                    $config['blockJsDirPath'] = $this->getRelPath($config['blockJsDirPath']);
                }
            }

            File::put('./create-acf-block.config.json', json_encode($config, JSON_PRETTY_PRINT));
        }

        $this->config = json_decode(File::get('./create-acf-block.config.json'), true);

        /**
         * Block Prompts
         */
        $blockName        = text(label: 'Block Name:', placeholder: "my-block-name", required: true);
        $blockTitle       = text(label: 'Block Title:', placeholder: "My Block Name", required: true);
        $blockDescription = text(label: 'Block Description:', placeholder: "A brief description of the block");
        $useJSX           = confirm(label: 'Use <InnerBlocks /> ?', default: false);

        /**
         * Block Registration File
         * All blocks are registered in $this->registrationFilePath
         * If the file doesn't exist create it.
         */
        $this->registrationFilePath = $this->config['registrationFileDir'] . '/register-acf-blocks.php';
        if (!File::exists($this->registrationFilePath)) {
            $contents = "<?php\n\n";
            $contents .= "// ACF Block Registration\n";
            $contents .= "\$blocks=array();\n\n";
            $contents .= "foreach (\$blocks as \$block) {\n";
            $contents .= "    register_block_type( get_template_directory() . '/" . $this->getNakedPath($this->config['blocksDirPath']) . "/' . \$block );\n";
            $contents .= "}\n";
            File::put($this->registrationFilePath, $contents);
        }

        /**
         * Add the block to the registration file
         */
        $contents = File::get($this->registrationFilePath);
        if (preg_match('/\$blocks\s*=\s*array\s*\(([^;]*)\)/', $contents, $matches)) {
            $arrayContents = $matches[1];

            // Transform the matched string into a real PHP array
            eval("\$parsedArray = array($arrayContents);");

            // Step 3: Insert a new item in the array.
            $newItem       = $blockName; // Replace with the desired string
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

        $this->blockDir = $this->config['blocksDirPath'] . '/' . $blockName;

        /** 
         * Create the block directory
         */
        if (!File::exists($this->blockDir)) {
            File::makeDirectory($this->blockDir, recursive: true);
        }

        /**
         * Create the block.php file
         */
        $blockPHPContents = File::get($this->stubDir . '/block.php.stub');
        $blockPHPContents = str_replace('{{blockName}}', $blockName, $blockPHPContents);
        $blockPHPContents = str_replace('{{blockTitle}}', $blockTitle, $blockPHPContents);
        $blockPHPContents = str_replace('{{blockDescription}}', $blockDescription, $blockPHPContents);
        $blockPHPContents = str_replace('{{blockDir}}', $this->blockDir, $blockPHPContents);
        File::put($this->blockDir . '/block.php', $blockPHPContents);

        /**
         * Create the template.php file
         */
        if ($useJSX) {
            $blockTemplateContents = File::get($this->stubDir . '/template-jsx.php.stub');
        } else {
            $blockTemplateContents = File::get($this->stubDir . '/template.php.stub');
        }
        $blockTemplateContents = str_replace('{{blockTitle}}', $blockTitle, $blockTemplateContents);
        $blockTemplateContents = str_replace('{{blockDescription}}', $blockDescription, $blockTemplateContents);
        File::put($this->blockDir . '/template.php', $blockTemplateContents);

        /**
         * Create the block.json file
         */
        if ($this->config['useBlockJSON']) {
            $jsonFileContents = [
                'name'        => $blockName,
                'title'       => $blockTitle,
                'description' => $blockDescription,
                'category'    => 'theme',
                'apiVersion'  => 2,
                'acf'         => [
                    'mode'           => 'preview',
                    'renderTemplate' => 'blocks/' . $blockName . '/block.php'
                ],
                'supports'    => [
                    'anchor' => true
                ]
            ];

            if ($useJSX) {
                $jsonFileContents['supports']['jsx'] = true;
            }


            File::put($this->blockDir . '/block.json', json_encode($jsonFileContents, JSON_PRETTY_PRINT));
        }

        /**
         * Create the block.css file
         */
        if ($this->config['blockAssets']) {
            if ($this->config['groupBlockAssets']) {
                $blockCSSContents = File::get($this->stubDir . '/block.css.stub');
                $blockCSSContents = str_replace('{{blockName}}', $blockName, $blockCSSContents);
                File::put($this->blockDir . '/block.css', $blockCSSContents);
                File::put($this->blockDir . '/block.js', '');
            } else {
                $blockCSSContents = File::get($this->stubDir . '/block.css.stub');
                $blockCSSContents = str_replace('{{blockName}}', $blockName, $blockCSSContents);
                File::put($this->config['blockCssDirPath'] . '/' . $blockName . '.css', $blockCSSContents);
                File::put($this->config['blockJsDirPath'] . '/' . $blockName . '.js', '');
            }
        }
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }

    public function getRelPath($path)
    {
        return '.' . str_replace(getcwd(), '', $path);
    }

    public function getNakedPath($path)
    {
        return str_replace('./', '', $path);
    }
}