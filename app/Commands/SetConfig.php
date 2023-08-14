<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use App\ConfigPrompts;

class SetConfig extends Command
{
    public $configPrompts;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'set-config';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Reruns the prompts to set the config file.';

    public function __construct(ConfigPrompts $configPrompts)
    {
        parent::__construct();
        $this->configPrompts = $configPrompts;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->configPrompts->handle(true);
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