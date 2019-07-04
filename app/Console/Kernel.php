<?php

namespace App\Console;


use App\Console\Commands\RepTransferCommand;
use App\Console\Commands\UserRepTransferCommand;
use App\Console\Commands\UserTransferCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        UserTransferCommand::class,
        RepTransferCommand::class,
        UserRepTransferCommand::class,
        //
        Commands\Statistics::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

    }
}
