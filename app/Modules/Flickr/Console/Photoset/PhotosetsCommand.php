<?php

namespace App\Modules\Flickr\Console\Photoset;

use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Core\Facades\Setting;
use App\Modules\Core\Services\TaskService;
use App\Modules\Flickr\Jobs\PhotosetsJob;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Console\Command;

class PhotosetsCommand extends Command
{
    public const COMMAND = 'flickr:photosets';

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
        $this->info('Fetching photosets ...');

        $repository->getCompleted('flickr')->each(function ($integration) use ($taskService) {
            $this->output->text('Processing integration: ' . $integration->name);

            $tasks = $taskService->tasks(
                FlickrService::TASK_PHOTOSETS,
                Setting::remember('flickr', 'task_contact_photos_limit', fn() => 10)
            );

            foreach ($tasks as $task) {
                $this->info('Processing ' . $task->task . ' with integration ' . $integration->name . ' for ' . $task->model->nsid);
                $model = $task->model;

                PhotosetsJob::dispatch($integration, $model->nsid)->onQueue(FlickrService::QUEUE_NAME);

                /**
                 * @TODO Should we take care if task completed successfully?
                 */
                $task->delete();
            }
        });
    }
}
