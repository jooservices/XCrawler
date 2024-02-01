<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Core\Models\Task;
use App\Modules\Core\StateMachine\Task\CompletedState;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Exceptions\Google\GoogleAlbumNotFound;
use App\Modules\Flickr\Models\FlickrPhoto;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\SerializesModels;

class PhotoUploadJob extends BaseJob implements ShouldBeUnique
{
    use SerializesModels;

    public $deleteWhenMissingModels = true;

    public function __construct(public Task $task)
    {
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->task->id;
    }

    /**
     * @return void
     * @throws GoogleAlbumNotFound
     */
    public function handle(): void
    {
        // Task already transitioned to InProgressState
        $this->task->transitionTo(InProgressState::class);

        $parentTask = $this->task->parentTask;
        if (!$parentTask->isState(InProgressState::class)) {
            $parentTask->transitionTo(InProgressState::class);
        }

        /**
         * @var FlickrPhoto $photo
         */
        $photo = $this->task->model;
        $photoset = $parentTask->model;

        if (!$photoset->googlePhotoAlbum()->exists()) {
            throw new GoogleAlbumNotFound('Google Photo Album not exists');
        }

        $photo->uploadToGooglePhotos($photoset->googlePhotoAlbum->album_id);
        $this->task->transitionTo(CompletedState::class);

        if ($parentTask->subTasks()->where('state_code', CompletedState::class)->count() === $parentTask->payload['photos']) {
            $parentTask->transitionTo(CompletedState::class);
        }
    }
}
