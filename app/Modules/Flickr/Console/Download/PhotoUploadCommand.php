<?php

namespace App\Modules\Flickr\Console\Download;

use App\Modules\Client\Services\GooglePhotos;
use App\Modules\Core\Console\Traits\HasTasksCommand;
use App\Modules\Core\Models\Task;
use App\Modules\Flickr\Jobs\PhotoUploadJob;
use App\Modules\Flickr\Services\TaskService;
use Illuminate\Console\Command;

class PhotoUploadCommand extends Command
{
    use HasTasksCommand;

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
     * @return void
     */
    public function handle(): void
    {
        $this->processTasks(TaskService::TASK_UPLOAD_PHOTO, 5, function (Task $task) {
            PhotoUploadJob::dispatch($task)->onQueue(GooglePhotos::QUEUE_NAME);
        });
    }
}
