<?php

namespace App\Modules\Flickr\Console\Photoset;

use App\Modules\Client\Exceptions\NoIntegrateException;
use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Core\Facades\Setting;
use App\Modules\Flickr\Jobs\PhotosetPhotosJob;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Services\TaskService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PhotosCommand extends Command
{
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

        $repository->getCompleted('flickr')->each(function ($integration) use ($taskService) {
            $this->output->text('Processing integration: ' . $integration->name);

            $tasks = $taskService->tasks(
                TaskService::TASK_PHOTOSET_PHOTOS,
                Setting::remember(
                    'flickr',
                    'task_' . Str::slug(TaskService::TASK_PHOTOSET_PHOTOS, '_') . '_limit',
                    fn() => 10
                )
            );

            foreach ($tasks as $task) {
                $this->info('Processing ' . $task->task . ' with integration ' . $integration->name . ' for ' . $task->model->id);

                PhotosetPhotosJob::dispatch($integration, $task)->onQueue(FlickrService::QUEUE_NAME);
            }
        });
    }
}
