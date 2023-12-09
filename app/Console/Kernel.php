<?php

namespace App\Console;

use App\Modules\Flickr\Console\Contact\FavoritesCommand;
use App\Modules\Flickr\Console\Contact\PhotosCommand;
use App\Modules\Flickr\Console\ContactCommand;
use App\Modules\Flickr\Console\PhotosSizesCommand;
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
    protected function schedule(Schedule $schedule): void
    {
        /**
         * JAV
         */
        $schedule->command('onejav:crawling-daily')->daily();
        $schedule->command('onejav:crawling-all');

        /**
         * Flickr
         */
        $schedule->command(ContactCommand::COMMAND)->weekly();
        $schedule->command(PhotosCommand::COMMAND)->everyTwoMinutes();
        $schedule->command(FavoritesCommand::COMMAND)->everyTwoMinutes();
        $schedule->command(PhotosSizesCommand::COMMAND)->everyTwoMinutes();
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
