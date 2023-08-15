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
        $this->pathService = $pathService;
        $this->stubDir     = $this->pathService->base_path('resources/stubs');
    }

    public function handle($config, $block)
    {
        $this->config = $config;
        $this->block  = $block;

        $this->createBlockDir();
        $this->createBlockController();
        $this->createBlockTemplate();
        $this->createBlockJSON();
        $this->createBlockAssets();
    }

    public function createBlockDir()
    {
        $this->blockDir = $this->pathService->getNakedPath($this->config['blocksDirPath']) . '/' . $this->block['name'];
        if (!File::exists($this->blockDir)) {
            File::makeDirectory($this->blockDir, recursive: true);
        }
    }

    public function createBlockController()
    {
        $blockPHPContents = File::get($this->stubDir . '/block.php.stub');
        $blockPHPContents = str_replace('{{blockName}}', $this->block['name'], $blockPHPContents);
        $blockPHPContents = str_replace('{{blockTitle}}', $this->block['title'], $blockPHPContents);
        $blockPHPContents = str_replace('{{blockDescription}}', $this->block['description'], $blockPHPContents);
        $blockPHPContents = str_replace('{{blockDir}}', $this->blockDir, $blockPHPContents);
        File::put($this->pathService->getNakedPath($this->blockDir) . '/block.php', $blockPHPContents);
    }

    public function createBlockTemplate()
    {
        if ($this->block['useJSX']) {
            $blockTemplateContents = File::get($this->stubDir . '/template-jsx.php.stub');
        } else {
            $blockTemplateContents = File::get($this->stubDir . '/template.php.stub');
        }
        $blockTemplateContents = str_replace('{{blockTitle}}', $this->block['title'], $blockTemplateContents);
        $blockTemplateContents = str_replace('{{blockDescription}}', $this->block['description'], $blockTemplateContents);
        File::put($this->pathService->getNakedPath($this->blockDir) . '/template.php', $blockTemplateContents);
    }

    public function createBlockJSON()
    {
        $jsonFileContents = [
            'name'        => $this->block['name'],
            'title'       => $this->block['title'],
            'description' => $this->block['description'],
            'category'    => 'theme',
            'apiVersion'  => 2,
            'acf'         => [
                'mode'           => 'preview',
                'renderTemplate' => $this->pathService->getNakedPath($this->blockDir) . '/block.php'
            ],
            'supports'    => [
                'anchor' => true
            ]
        ];

        if ($this->block['useJSX']) {
            $jsonFileContents['supports']['jsx'] = true;
        }

        File::put($this->pathService->getNakedPath($this->blockDir) . '/block.json', json_encode($jsonFileContents, JSON_PRETTY_PRINT));
    }

    public function createBlockAssets()
    {
        if ($this->config['blockAssets']) {
            if ($this->config['groupBlockAssets']) {
                $blockCSSContents = File::get($this->stubDir . '/block.css.stub');
                $blockCSSContents = str_replace('{{blockName}}', $this->block['name'], $blockCSSContents);
                File::put($this->pathService->getNakedPath($this->blockDir) . '/block.css', $blockCSSContents);
                File::put($this->pathService->getNakedPath($this->blockDir) . '/block.js', '');
            } else {
                $blockCSSContents = File::get($this->stubDir . '/block.css.stub');
                $blockCSSContents = str_replace('{{blockName}}', $this->block['name'], $blockCSSContents);
                File::put($this->config['blockCssDirPath'] . '/' . $this->block['name'] . '.css', $blockCSSContents);
                File::put($this->config['blockJsDirPath'] . '/' . $this->block['name'] . '.js', '');
            }
        }
    }
}