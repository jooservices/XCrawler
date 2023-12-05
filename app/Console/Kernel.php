<?php

namespace App\Console;

use App\Modules\Core\Facades\Setting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /**
         * JAV
         */
        $schedule->command('onejav:crawling-daily')->daily();
        $schedule->command('onejav:crawling-all');

        if (Setting::remember('flickr', 'enable_schedule', false)) {
            /**
             * Flickr
             */
            $schedule->command('flickr:contacts')->weekly();
            $schedule->command('flickr:people-photos')->everyFiveMinutes();
            $schedule->command('flickr:contact-favorites')->everyFiveMinutes();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
