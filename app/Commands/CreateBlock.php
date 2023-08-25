<?php

namespace App\Commands;

use App\BlockPrompts;
use App\BlockRegistration;
use App\BlockScaffold;
use App\ConfigPrompts;
use App\Services\ConfigService;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

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
    public $configService;

    // Properties
    public $config;
    public $block;

    // public function __construct(ConfigPrompts $configPrompts, BlockPrompts $blockPrompts, BlockRegistration $blockRegistration, BlockScaffold $blockScaffold)
    // {
    //     parent::__construct();
    //     $this->configPrompts     = $configPrompts;
    //     $this->blockPrompts      = $blockPrompts;
    //     $this->blockRegistration = $blockRegistration;
    //     $this->blockScaffold     = $blockScaffold;
    // }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // $this->configPrompts = spin(function () {
        //     return app(ConfigPrompts::class);
        // }, 'Initializing...');

        // spin(function () {
        //     $this->configPrompts = app(ConfigPrompts::class);
        //     return true;
        // }, 'Preparing CLI...');
        $this->configService = app(ConfigService::class);

        if (!$this->configService->configIsSet()) {
            // Run the config prompts
            $this->configPrompts = app(ConfigPrompts::class)->handle();
        }

        $this->blockPrompts      = app(BlockPrompts::class);
        $this->blockRegistration = app(BlockRegistration::class);
        $this->blockScaffold     = app(BlockScaffold::class);

        // Run the block prompts
        $this->block = $this->blockPrompts->handle();

        // Register the block
        $this->config = $this->configService->config;
        $this->blockRegistration->handle($this->config, $this->block);

        // Scaffold the block
        $this->blockScaffold->handle($this->config, $this->block);
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