<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Core\Models\Task;
use App\Modules\Core\Services\FileManager;
use App\Modules\Flickr\Events\PhotosetPhotoDownloadCompletedEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

class DownloadPhotoJob extends BaseJob
{
    use SerializesModels;

    public $deleteWhenMissingModels = true;

    /**
     * @description dowload-photoset-photo task
     * @param Task $task
     */
    public function __construct(public Task $task)
    {
    }

    public function handle(FileManager $fileManager)
    {
        $photo = $this->task->model;
        $fileName = $fileManager->download($photo->getOriginalSizeUrl());

        $this->task->update([
            'payload' => $fileName,
        ]);

        Event::dispatch(new PhotosetPhotoDownloadCompletedEvent($this->task));
        /**
         * @TODO Instance create job for upload to cloud
         */
    }
}
