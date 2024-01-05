<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Core\Models\Task;
use App\Modules\Core\Services\FileManager;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\PhotosetPhotoDownloadCompletedEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

class DownloadPhotoJob extends BaseJob
{
    use SerializesModels;

    public $deleteWhenMissingModels = true;

    public function __construct(public Task $task)
    {
    }

    public function handle(FileManager $fileManager)
    {
        Storage::fake('local');
        $photo = $this->task->model;
        $sizes = $photo->getSizes();
        $size = end($sizes);

        $savedFile = $fileManager->download($size['source']);

        $this->task->update(['state_code' => States::STATE_COMPLETED]);

        Event::dispatch(new PhotosetPhotoDownloadCompletedEvent($this->task));
    }
}
