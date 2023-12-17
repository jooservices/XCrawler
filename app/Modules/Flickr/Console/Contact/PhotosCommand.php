<?php

namespace App\Modules\Flickr\Console\Contact;

use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Core\Facades\Setting;
use App\Modules\Core\Services\TaskService;
use App\Modules\Flickr\Jobs\ContactPhotosJob;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class PhotosCommand extends Command implements Isolatable
{
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
     * @param TaskService $taskService
     * @return void
     */
    public function handle(TaskService $taskService, IntegrationRepository $repository): void
    {
        $this->info('Fetching contact\' photos ...');

        $repository->getCompleted('flickr')->each(function ($integration) use ($taskService) {
            $this->output->text('Processing integration: ' . $integration->name);

            $tasks = $taskService->tasks(
                FlickrService::TASK_CONTACT_PHOTOS,
                Setting::remember(
                    'flickr',
                    'task_contact_photos_limit',
                    fn() => config('flickr.task_limit', 10)
                )
            );

            foreach ($tasks as $task) {
                $this->info(
                    'Processing ' . $task->task . ' with integration ' . $integration->name . ' for ' . $task->model->nsid
                );
                $model = $task->model;

                ContactPhotosJob::dispatch($integration, $model->nsid)->onQueue(FlickrService::QUEUE_NAME);

                /**
                 * @TODO Should we take care if task completed successfully?
                 */
                $task->delete();
            }
        });
    }
}
