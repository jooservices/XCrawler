<?php

namespace App\Modules\Flickr\Console\Contact;

use App\Modules\Core\Facades\Setting;
use App\Modules\Core\Services\States;
use App\Modules\Core\Services\TaskService;
use App\Modules\Flickr\Jobs\ContactFavoritesJob;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Console\Command;

class FavoritesCommand extends Command
{
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
    protected $description = '';

    /**
     * @param TaskService $taskService
     * @return void
     */
    public function handle(TaskService $taskService): void
    {
        $tasks = $taskService->tasks(
            FlickrService::TASK_CONTACT_FAVORITES,
            Setting::remember('flickr', 'task_contact_favorites_limit', fn() => 10)
        );

        foreach ($tasks as $task) {
            $model = $task->model;
            ContactFavoritesJob::dispatch($model->nsid)->onQueue('flickr');
            /**
             * @TODO Should we take care if task completed successfully?
             */
            $task->delete();
            /**
             * Create new same task for next run.
             */

            $model->tasks()->create([
                'task' => FlickrService::TASK_CONTACT_FAVORITES,
                'state_code' => States::STATE_INIT,
            ]);
        }
    }
}
