<?php

namespace App\Modules\Flickr\Console\Contact;

use App\Modules\Client\Exceptions\NoIntegrateException;
use App\Modules\Core\Facades\Setting;
use App\Modules\Flickr\Console\Traits\HasIntegrationProcess;
use App\Modules\Flickr\Jobs\ContactPhotosJob;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Services\TaskService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class PhotosCommand extends Command implements Isolatable
{
    use HasIntegrationProcess;

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
     * @throws NoIntegrateException
     */
    public function handle(TaskService $taskService): void
    {
        $this->info('Fetching contact\' photos ...');

        $this->completed(FlickrService::SERVICE_NAME, function ($integration) use ($taskService) {
            $tasks = $taskService->tasks(
                TaskService::TASK_CONTACT_PHOTOS,
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

                ContactPhotosJob::dispatch($integration, $task)->onQueue(FlickrService::QUEUE_NAME);
            }
        });
    }
}
