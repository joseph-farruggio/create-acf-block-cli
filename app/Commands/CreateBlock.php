<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

use App\ConfigPrompts;
use App\BlockPrompts;
use App\BlockRegistration;
use App\BlockScaffold;

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

    //  Tasks
    public $configPrompts;
    public $blockPrompts;
    public $blockRegistration;
    public $blockScaffold;

    // Properties
    public $config;

    public function __construct(ConfigPrompts $configPrompts, BlockPrompts $blockPrompts, BlockRegistration $blockRegistration, BlockScaffold $blockScaffold)
    {
        parent::__construct();
        $this->configPrompts     = $configPrompts;
        $this->blockPrompts      = $blockPrompts;
        $this->blockRegistration = $blockRegistration;
        $this->blockScaffold     = $blockScaffold;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Run the config prompts
        $this->configPrompts->handle($this);

        // Get the config file
        $this->config = $this->configPrompts->getConfig();

        // Run the block prompts
        $this->blockPrompts->handle();

        // Get the block
        $block = $this->blockPrompts->getBlock();

        // Register the block
        $this->blockRegistration->handle($block);

        // Scaffold the block
        $this->blockScaffold->handle($block);
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
}