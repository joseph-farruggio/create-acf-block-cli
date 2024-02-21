<?php

namespace App;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use function Laravel\Prompts\text;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\note;
use function Laravel\Prompts\intro;

use App\Timer;
use App\Services\ConfigService;

class BlockPrompts
{
    public $block;
    public $blockSchema;
    public $configService;


    public function handle()
    {
        $this->configService = app(ConfigService::class);
        if (!$this->configService->get('blockSlugify')) {
            $blockName = text(
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
        }
        intro("Create block:");
        $blockTitle       = text(label: 'Block Title:', placeholder: "My Block Name", required: true);
        $blockDescription = text(label: 'Block Description:', placeholder: "A brief description of the block");
        // $useJSX           = confirm(label: 'Use <InnerBlocks /> ?', default: false);

        return [
            'name'        => $blockName ?? Str::of($blockTitle)->slug('-'),
            'title'       => $blockTitle,
            'description' => $blockDescription,
            // 'useJSX'      => $useJSX,
        ];
    }
}