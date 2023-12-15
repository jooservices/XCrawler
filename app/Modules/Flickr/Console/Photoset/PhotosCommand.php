<?php

namespace App\Modules\Flickr\Console\Photoset;

use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Core\Facades\Setting;
use App\Modules\Core\Services\TaskService;
use App\Modules\Flickr\Jobs\PhotosetsJob;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Services\FlickrService;
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
    protected $description = '';

    /**
     * @param TaskService $taskService
     * @return void
     */
    public function handle(TaskService $taskService, IntegrationRepository $repository): void
    {
        $this->info('Fetching photos ...');

        $repository->getCompleted('flickr')->each(function ($integration) use ($taskService) {
            $this->output->text('Processing integration: ' . $integration->name);

            $tasks = $taskService->tasks(
                FlickrPhotoset::TASK_PHOTOSET_PHOTOS,
                Setting::remember(
                    'flickr',
                    'task_' . Str::slug(FlickrService::TASK_PHOTOSET_PHOTOS, '_') . '_limit',
                    fn() => 10
                )
            );

            foreach ($tasks as $task) {
                $this->info('Processing ' . $task->task . ' with integration ' . $integration->name . ' for ' . $task->model->nsid);
                $model = $task->model;

                PhotosetsJob::dispatch($integration, $model->owner)->onQueue(FlickrService::QUEUE_NAME);

                /**
                 * @TODO Should we take care if task completed successfully?
                 */
                $task->delete();
            }
        });
    }
}
