<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\CloudinaryMigrateProductImages;
use App\Console\Commands\ResolveAppointmentBranches;
use App\Console\Commands\SendDueReminders;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        SendDueReminders::class,
        CloudinaryMigrateProductImages::class,
        ResolveAppointmentBranches::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('reminders:send')->everyMinute();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}

