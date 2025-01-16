<?php

namespace App\Console;

use App\Console\Commands\DeleteTempUploadedFiles;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        if (app()->environment() === 'production') {
            $schedule->command('opencast:finished-events')->everyFiveMinutes()->withoutOverlapping();
            $schedule->command('app:update-assets-symbolic-links')->everyFiveMinutes();
        }
        $schedule->command('app:check-time-availability-clips')->everyFiveMinutes();
        $schedule->command('app:check-livestreams')->everyFiveMinutes();
        $schedule->command('app:enable-livestreams')->everyFiveMinutes();
        $schedule->command('app:check-for-expired-users')->dailyAt('13:00');
        $schedule->command(DeleteTempUploadedFiles::class)->hourly();
    }
}
