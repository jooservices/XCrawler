<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Core\Models\Task;
use App\Modules\Core\StateMachine\Task\CompletedState;
use App\Modules\Core\StateMachine\Task\InProgressState;
use Exception;
use Illuminate\Queue\SerializesModels;

class PhotoUploadJob extends BaseJob
{
    use SerializesModels;

    public $deleteWhenMissingModels = true;

    public function __construct(public Task $task)
    {
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        $this->task->state_code->transitionTo(InProgressState::class);
        $parentTask = $this->task->parentTask;
        $photo = $this->task->model;
        $photoset = $parentTask->model;

        if (!$photoset->googlePhotoAlbum()->exists()) {
            throw new Exception('Google Photo Album not exists');
        }

        $photo->uploadToGooglePhotos($photoset->googlePhotoAlbum->album_id);
        $this->task->state_code->transitionTo(CompletedState::class);

        if ($parentTask->subTasks()->where('state_code', CompletedState::class)->count() === $parentTask->payload['photos']) {
            $parentTask->state_code->transitionTo(CompletedState::class);
        }
    }
}
