<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Core\Models\Task;
use App\Modules\Core\Services\FileManager;
use App\Modules\Core\StateMachine\Task\DownloadedState;
use App\Modules\Core\StateMachine\Task\InProgressState;
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

    public function handle(): void
    {
        // Photo already downloaded
        if ($this->task->isState(DownloadedState::class)) {
            $this->task->update([
                'payload' => $this->download(),
            ]);

            Event::dispatch(new PhotosetPhotoDownloadCompletedEvent($this->task));
            return;
        }

        $this->task->transitionTo(InProgressState::class);
        $this->task->update([
            'payload' => $this->download(),
        ]);

        Event::dispatch(new PhotosetPhotoDownloadCompletedEvent($this->task));
    }

    private function download(): int
    {
        $fileManager = app(FileManager::class);

        return $fileManager->download($this->task->model->getOriginalSizeUrl());
    }
}
