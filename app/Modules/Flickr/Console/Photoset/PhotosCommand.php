<?php

namespace App\Modules\Flickr\Console\Photoset;

use App\Modules\Client\Exceptions\NoIntegrateException;
use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Core\Console\Traits\HasIntegrationsCommand;
use App\Modules\Core\Console\Traits\HasTasksCommand;
use App\Modules\Core\Facades\Setting;
use App\Modules\Flickr\Jobs\PhotosetPhotosJob;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Services\TaskService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PhotosCommand extends Command
{
    use HasIntegrationsCommand;
    use HasTasksCommand;

    public const COMMAND = 'flickr:photoset-photos';

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
    protected $description = 'Fetch photos for photosets';

    /**
     * @param TaskService $taskService
     * @param IntegrationRepository $repository
     * @return void
     * @throws NoIntegrateException
     */
    public function handle(TaskService $taskService, IntegrationRepository $repository): void
    {
        $this->info('Fetching photos ...');

        $this->processCompletedIntegrations(FlickrService::SERVICE_NAME, function ($integration) use ($taskService) {
            $this->processTasks(
                TaskService::TASK_PHOTOSET_PHOTOS,
                Setting::remember(
                    'flickr',
                    'task_' . Str::slug(TaskService::TASK_PHOTOSET_PHOTOS, '_') . '_limit',
                    fn() => config('flickr.task_limit', 10)
                ),
                function ($task) use ($integration) {
                    PhotosetPhotosJob::dispatch($integration, $task)->onQueue(FlickrService::QUEUE_NAME);
                }
            );
        });
    }
}
