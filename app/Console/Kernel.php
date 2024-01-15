<?php

namespace App\Console;

use App\Modules\Flickr\Console\Contact\FavoritesCommand;
use App\Modules\Flickr\Console\Contact\PhotosCommand;
use App\Modules\Flickr\Console\Contact\PhotosetsCommand;
use App\Modules\Flickr\Console\ContactsCommand;
use App\Modules\Flickr\Console\Download\PhotoUploadCommand;
use App\Modules\Flickr\Console\Photoset\PhotosCommand as PhotosetPhotosCommand;
use App\Modules\Flickr\Console\PhotosSizesCommand;
use App\Modules\JAV\Console\Onejav\DailyCommand;
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
        $schedule->command(DailyCommand::COMMAND)->daily();
        $schedule->command('onejav:all');

        /**
         * Flickr
         */
        $schedule->command(ContactsCommand::COMMAND)->weekly();
        $schedule->command(PhotosCommand::COMMAND)->everyTwoMinutes();
        $schedule->command(FavoritesCommand::COMMAND)->everyTwoMinutes();

        $schedule->command(PhotosetsCommand::COMMAND)->everyTwoMinutes();
        $schedule->command(PhotosetPhotosCommand::COMMAND)->everyTwoMinutes();

        $schedule->command(PhotosSizesCommand::COMMAND)->everyTwoMinutes();

        $schedule->command(PhotoUploadCommand::COMMAND)->everyMinute();
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
