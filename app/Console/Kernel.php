<?php

namespace App\Console;

use App\Console\Commands\BalanceOperationCommand;
use App\Console\Commands\UserAddCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        UserAddCommand::class,
        BalanceOperationCommand::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        //
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
