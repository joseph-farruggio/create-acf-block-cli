<?php

namespace App;

use App\Services\PathService;
use Illuminate\Support\Facades\File;

class BlockScaffold
{
    public $pathService;
    public $config;
    public $blockDir;
    public $stubDir;
    public $block;


    public function __construct(PathService $pathService)
    {
        if (File::exists('./acf-block-cli.config.json')) {
            $this->config = json_decode(File::get('./acf-block-cli.config.json'), true);
        } else {
            $this->config = [];
        }

        $this->pathService = $pathService;
        $this->stubDir     = $this->pathService->base_path('resources/stubs');
    }

    public function handle($block)
    {
        $this->block = $block;

        /** 
         * Create the block directory
         */
        $this->blockDir = $this->config['blocksDirPath'] . '/' . $block['name'];
        if (!File::exists($this->blockDir)) {
            File::makeDirectory($this->blockDir, recursive: true);
        }

        /**
         * Create the block.php file
         */
        $blockPHPContents = File::get($this->stubDir . '/block.php.stub');
        $blockPHPContents = str_replace('{{blockName}}', $block['name'], $blockPHPContents);
        $blockPHPContents = str_replace('{{blockTitle}}', $block['title'], $blockPHPContents);
        $blockPHPContents = str_replace('{{blockDescription}}', $block['description'], $blockPHPContents);
        $blockPHPContents = str_replace('{{blockDir}}', $this->blockDir, $blockPHPContents);
        File::put($this->blockDir . '/block.php', $blockPHPContents);

        /**
         * Create the template.php file
         */
        if ($block['useJSX']) {
            $blockTemplateContents = File::get($this->stubDir . '/template-jsx.php.stub');
        } else {
            $blockTemplateContents = File::get($this->stubDir . '/template.php.stub');
        }
        $blockTemplateContents = str_replace('{{blockTitle}}', $block['title'], $blockTemplateContents);
        $blockTemplateContents = str_replace('{{blockDescription}}', $block['description'], $blockTemplateContents);
        File::put($this->blockDir . '/template.php', $blockTemplateContents);

        /**
         * Create the block.json file
         */
        if ($this->config['useBlockJSON']) {
            $jsonFileContents = [
                'name'        => $block['name'],
                'title'       => $block['title'],
                'description' => $block['description'],
                'category'    => 'theme',
                'apiVersion'  => 2,
                'acf'         => [
                    'mode'           => 'preview',
                    'renderTemplate' => 'blocks/' . $block['name'] . '/block.php'
                ],
                'supports'    => [
                    'anchor' => true
                ]
            ];

            if ($block['useJSX']) {
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
                $blockCSSContents = str_replace('{{blockName}}', $block['name'], $blockCSSContents);
                File::put($this->blockDir . '/block.css', $blockCSSContents);
                File::put($this->blockDir . '/block.js', '');
            } else {
                $blockCSSContents = File::get($this->stubDir . '/block.css.stub');
                $blockCSSContents = str_replace('{{blockName}}', $block['name'], $blockCSSContents);
                File::put($this->config['blockCssDirPath'] . '/' . $block['name'] . '.css', $blockCSSContents);
                File::put($this->config['blockJsDirPath'] . '/' . $block['name'] . '.js', '');
            }
        }
    }

    public function getConfig()
    {
        return json_decode(File::get('./acf-block-cli.config.json'), true);
    }
}