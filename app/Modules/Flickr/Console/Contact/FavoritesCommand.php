<?php

namespace App\Modules\Flickr\Console\Contact;

use App\Modules\Client\Exceptions\NoIntegrateException;
use App\Modules\Core\Console\Traits\HasIntegrationsCommand;
use App\Modules\Core\Console\Traits\HasTasksCommand;
use App\Modules\Core\Facades\Setting;
use App\Modules\Flickr\Jobs\ContactFavoritesJob;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Services\TaskService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

/**
 * @test App\Modules\Flickr\Console\Contact\FavoritesCommandTest
 */
class FavoritesCommand extends Command implements Isolatable
{
    use HasIntegrationsCommand;
    use HasTasksCommand;

    public const COMMAND = 'flickr:contact-favorites';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch contact\'s favorites photos';

    /**
     * @return void
     * @throws NoIntegrateException
     */
    public function handle(): void
    {
        $this->info('Fetching favorites\' photos ...');

        $this->processCompletedIntegrations(FlickrService::SERVICE_NAME, function ($integration) {
            $this->processTasks(
                TaskService::TASK_CONTACT_FAVORITES,
                Setting::remember(
                    'flickr',
                    'task_contact_favorites_limit',
                    fn() => config('flickr.task_limit', 10)
                ),
                function ($task) use ($integration) {
                    ContactFavoritesJob::dispatch($integration, $task)->onQueue(FlickrService::QUEUE_NAME);
                }
            );
        });
    }
}
