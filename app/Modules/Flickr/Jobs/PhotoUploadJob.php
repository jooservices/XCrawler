<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Core\Models\Task;
use App\Modules\Core\StateMachine\Task\CompletedState;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Exceptions\Google\GoogleAlbumNotFound;
use App\Modules\Flickr\Models\FlickrPhoto;
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
        $this->task->transitionTo(InProgressState::class);

        $parentTask = $this->task->parentTask;
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
