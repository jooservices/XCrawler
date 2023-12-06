<?php

namespace App\Modules\Flickr\Console\Contact;

use App\Modules\Core\Facades\Setting;
use App\Modules\Core\Services\States;
use App\Modules\Core\Services\TaskService;
use App\Modules\Flickr\Jobs\ContactPhotosJob;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Console\Command;

class PhotosCommand extends Command
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
    protected $description = '';

    /**
     * @param TaskService $taskService
     * @return void
     */
    public function handle(TaskService $taskService): void
    {
        $tasks = $taskService->tasks(
            FlickrService::TASK_CONTACT_PHOTOS,
            Setting::remember('flickr', 'task_contact_photos_limit', fn() => 10)
        );

        foreach ($tasks as $task) {
            $model = $task->model;
            ContactPhotosJob::dispatch($model->nsid)->onQueue('flickr');
            /**
             * @TODO Should we take care if task completed successfully?
             */
            $task->delete();
            /**
             * Create new same task for next run.
             */

            $model->tasks()->create([
                'task' => FlickrService::TASK_CONTACT_PHOTOS,
                'state_code' => States::STATE_INIT,
            ]);
        }
    }
}
