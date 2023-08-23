<?php

namespace App;

use Illuminate\Support\Facades\File;
use function Laravel\Prompts\text;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\note;

use App\Services\PathService;

class BlockPrompts
{
    public $block;
    public $pathService;
    public $blockSchema;

    public function __construct(PathService $pathService)
    {
        $this->pathService = $pathService;
    }

    public function handle()
    {
        $blockName        = text(
            label: 'Block Name:',
            placeholder: "my-block-name",
            required: true,
            validate: fn(string $value) => match (true) {
                strlen($value) < 3 => 'The name must be at least 3 characters.',
                strlen($value) > 255 => 'The name must not exceed 255 characters.',
                preg_match('/^[a-z][a-z0-9-]*$/', $value) !== 1 => 'The block name must start with a lowercase letter and may only contain lowercase letters, numbers, or dashes.',
                default => null
            }
        );
        $blockTitle       = text(label: 'Block Title:', placeholder: "My Block Name", required: true);
        $blockDescription = text(label: 'Block Description:', placeholder: "A brief description of the block");
        $useJSX           = confirm(label: 'Use <InnerBlocks /> ?', default: false);

        return [
            'name'        => $blockName,
            'title'       => $blockTitle,
            'description' => $blockDescription,
            'useJSX'      => $useJSX,
        ];
    }
}