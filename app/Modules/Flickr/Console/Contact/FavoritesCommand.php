<?php

namespace App\Modules\Flickr\Console\Contact;

use App\Modules\Client\Exceptions\NoIntegrateException;
use App\Modules\Core\Facades\Setting;
use App\Modules\Core\Services\TaskService;
use App\Modules\Flickr\Console\Traits\HasIntegrationProcess;
use App\Modules\Flickr\Jobs\ContactFavoritesJob;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class FavoritesCommand extends Command implements Isolatable
{
    use HasIntegrationProcess;

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
    protected $description = 'Fetch contact\'s favorites photos';

    /**
     * @param TaskService $taskService
     * @return void
     * @throws NoIntegrateException
     */
    public function handle(TaskService $taskService): void
    {
        $this->info('Fetching favorites\' photos ...');

        $this->completed(FlickrService::SERVICE_NAME, function ($integration) use ($taskService) {
            $tasks = $taskService->tasks(
                FlickrService::TASK_CONTACT_FAVORITES,
                Setting::remember(
                    'flickr',
                    'task_contact_favorites_limit',
                    fn() => config('flickr.task_limit', 10)
                )
            );

            foreach ($tasks as $task) {
                $this->info('Processing ' . $task->task . ' with integration ' . $integration->name . ' for ' . $task->model->nsid);

                ContactFavoritesJob::dispatch($integration, $task)->onQueue(FlickrService::QUEUE_NAME);
            }
        });
    }
}
