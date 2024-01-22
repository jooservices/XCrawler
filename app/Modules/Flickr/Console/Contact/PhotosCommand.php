<?php

namespace App\Modules\Flickr\Console\Contact;

use App\Modules\Client\Exceptions\NoIntegrateException;
use App\Modules\Core\Console\Traits\HasIntegrationsCommand;
use App\Modules\Core\Console\Traits\HasTasksCommand;
use App\Modules\Core\Facades\Setting;
use App\Modules\Flickr\Jobs\ContactPhotosJob;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Services\TaskService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class PhotosCommand extends Command implements Isolatable
{
    use HasIntegrationsCommand;
    use HasTasksCommand;

    public const COMMAND = 'flickr:contact-photos';

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
    protected $description = 'Fetch contact\'s photos';

    /**
     * @return void
     * @throws NoIntegrateException
     */
    public function handle(): void
    {
        $this->info('Fetching contact\' photos ...');

        $this->processCompletedIntegrations(FlickrService::SERVICE_NAME, function ($integration) {
            $this->processTasks(
                TaskService::TASK_CONTACT_PHOTOS,
                Setting::remember(
                    'flickr',
                    'task_contact_photos_limit',
                    fn() => config('flickr.task_limit', 10)
                ),
                function ($task) use ($integration) {
                    ContactPhotosJob::dispatch($integration, $task)->onQueue(FlickrService::QUEUE_NAME);
                }
            );
        });
    }
}
