<?php

namespace App;

use App\Services\ConfigService;
use App\Services\PathService;
use Illuminate\Support\Facades\File;
use App\Timer;
use function Laravel\Prompts\outro;

class BlockScaffold
{
    public $pathService;
    public $configService;
    public $config;
    public $blockDir;
    public $stubDir;
    public $block;


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

        $this->createBlockDir();
        $this->createBlockController();
        $this->createBlockTemplate();
        $this->createBlockJSON();
        $this->createBlockAssets();
        outro("Block created at: {$this->blockDir}");
    }

    public function createBlockDir()
    {
        $this->blockDir = $this->pathService->getNakedPath($this->configService->get('blocksDirPath')) . '/' . $this->block['name'];
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
            $blockTemplateContents = File::get($this->stubDir . '/render-jsx.php.stub');
        } else {
            $blockTemplateContents = File::get($this->stubDir . '/render.php.stub');
        }
        $blockTemplateContents = str_replace('{{blockTitle}}', $this->block['title'], $blockTemplateContents);
        $blockTemplateContents = str_replace('{{blockDescription}}', $this->block['description'], $blockTemplateContents);
        File::put($this->pathService->getNakedPath($this->blockDir) . '/render.php', $blockTemplateContents);
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
        if ($this->configService->get('blockAssets')) {
            if ($this->configService->get('groupBlockAssets')) {
                $blockCSSContents = File::get($this->stubDir . '/block.css.stub');
                $blockCSSContents = str_replace('{{blockName}}', $this->block['name'], $blockCSSContents);
                File::put($this->pathService->getNakedPath($this->blockDir) . '/block.css', $blockCSSContents);
                File::put($this->pathService->getNakedPath($this->blockDir) . '/block.js', '');
            } else {
                $blockCSSContents = File::get($this->stubDir . '/block.css.stub');
                $blockCSSContents = str_replace('{{blockName}}', $this->block['name'], $blockCSSContents);
                File::put($this->configService->get('blockCssDirPath') . '/' . $this->block['name'] . '.css', $blockCSSContents);
                File::put($this->configService->get('blockJsDirPath') . '/' . $this->block['name'] . '.js', '');
            }
        }
    }
}