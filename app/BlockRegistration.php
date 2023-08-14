<?php

namespace App;

use Illuminate\Support\Facades\File;
use App\ConfigPrompts;
use App\Services\PathService;

class BlockRegistration
{
    public $config;
    public $configPrompts;
    public $registrationFilePath;
    public $pathService;

    public function __construct(ConfigPrompts $configPrompts, PathService $pathService)
    {
        $this->configPrompts = $configPrompts;
        $this->config        = $this->configPrompts->getConfig();
    }

    public function handle($block)
    {
        /**
         * Block Registration File
         * All blocks are registered in $this->registrationFilePath
         * If the file doesn't exist create it.
         */
        if ($this->config['createRegistrationFile']) {
            $this->registrationFilePath = $this->config['registrationFileDir'] . '/register-acf-blocks.php';
            if (!File::exists($this->registrationFilePath)) {
                $contents = "<?php\n\n";
                $contents .= "// ACF Block Registration\n";
                $contents .= "\$blocks=array();\n\n";
                $contents .= "foreach (\$blocks as \$block) {\n";
                $contents .= "    register_block_type( get_template_directory() . '/" . $this->pathService->getNakedPath($this->config['blocksDirPath']) . "/' . \$block );\n";
                $contents .= "}\n";
                File::put($this->registrationFilePath, $contents);
            }
        }

        /**
         * Add the block to the registration file
         */
        if ($this->config['createRegistrationFile']) {
            $contents = File::get($this->registrationFilePath);
            if (preg_match('/\$blocks\s*=\s*array\s*\(([^;]*)\)/', $contents, $matches)) {
                $arrayContents = $matches[1];

                // Transform the matched string into a real PHP array
                eval("\$parsedArray = array($arrayContents);");

                // Step 3: Insert a new item in the array.
                $newItem       = $block['name']; // Replace with the desired string
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
}