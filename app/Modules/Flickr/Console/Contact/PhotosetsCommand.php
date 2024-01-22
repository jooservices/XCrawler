<?php

namespace App\Modules\Flickr\Console\Contact;

use App\Modules\Client\Exceptions\NoIntegrateException;
use App\Modules\Core\Console\Traits\HasIntegrationsCommand;
use App\Modules\Core\Console\Traits\HasTasksCommand;
use App\Modules\Core\Facades\Setting;
use App\Modules\Flickr\Jobs\PhotosetsJob;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Services\TaskService;
use Illuminate\Console\Command;

class PhotosetsCommand extends Command
{
    use HasIntegrationsCommand;
    use HasTasksCommand;

    public const COMMAND = 'flickr:contact-photosets';

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
    protected $description = '';

    /**
     * @return void
     * @throws NoIntegrateException
     */
    public function handle(): void
    {
        $this->info('Fetching photosets ...');

        $this->processCompletedIntegrations(FlickrService::SERVICE_NAME, function ($integration) {
            $this->processTasks(
                TaskService::TASK_CONTACT_PHOTOSETS,
                Setting::remember(
                    'flickr',
                    'task_contact_photosets_limit',
                    fn() => config('flickr.task_limit', 10)
                ),
                function ($task) use ($integration) {
                    PhotosetsJob::dispatch($integration, $task)->onQueue(FlickrService::QUEUE_NAME);
                }
            );
        });
    }
}
