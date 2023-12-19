<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Core\Models\Task;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Queue\SerializesModels;

class PhotosetPhotosJob extends BaseJob
{
    use SerializesModels;

    public $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Integration $integration, public Task $task, public int $page = 1)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FlickrService $flickrService): void
    {
        $flickrService->setIntegration($this->integration);
        $photoset = $this->task->model;
        $adapter = $flickrService->photosets;

        $items = $adapter->getPhotos([
            'user_id' => $photoset->owner,
            'photoset_id' => $photoset->id,
            'page' => $this->page
        ]);

        $items->getItems()->each(function ($photo) use ($photoset) {
            $photo = FlickrPhoto::updateOrCreate([
                'id' => $photo['id'],
                'owner' => $photoset->owner,
            ], $photo);

            $photoset->relationshipPhotos()->syncWithoutDetaching([$photo->id]);
        });

        if ($items->isCompleted()) {
            $this->task->updateState(States::STATE_COMPLETED);
            return;
        }

        self::dispatch($this->integration, $this->task, $items->getNextPage())
            ->onQueue(FlickrService::QUEUE_NAME);
    }
}
