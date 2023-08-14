<?php

namespace App;

use function Laravel\Prompts\text;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\note;

class BlockPrompts
{

    public $block;
    public function handle()
    {
        $blockName        = text(label: 'Block Name:', placeholder: "my-block-name", required: true);
        $blockTitle       = text(label: 'Block Title:', placeholder: "My Block Name", required: true);
        $blockDescription = text(label: 'Block Description:', placeholder: "A brief description of the block");
        $useJSX           = confirm(label: 'Use <InnerBlocks /> ?', default: false);

        $this->block = [
            'name'        => $blockName,
            'title'       => $blockTitle,
            'description' => $blockDescription,
            'useJSX'      => $useJSX,
        ];
    }

    public function getBlock()
    {
        return $this->block;
    }
}