<?php

namespace App\Modules\Flickr\Console\Download;

use App\Modules\Core\Repositories\TaskRepository;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Jobs\PhotoUploadJob;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Services\TaskService;
use Illuminate\Console\Command;

class PhotoUploadCommand extends Command
{
    public const COMMAND = 'flickr:photo-upload';
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
    protected $description = 'Download all photos\'s album.';

    /**
     * @param TaskRepository $taskRepository
     * @return void
     */
    public function handle(TaskRepository $taskRepository): void
    {
        $taskRepository->tasks(TaskService::TASK_UPLOAD_PHOTO, 5)->each(function ($task) {
            PhotoUploadJob::dispatch($task)->onQueue(FlickrService::QUEUE_NAME);
        });
    }
}
