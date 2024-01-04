<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Core\Models\Task;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\FetchPhotosetPhotosCompletedEvent;
use App\Modules\Flickr\Events\RecurredTaskEvent;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

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
     * @param FlickrService $flickrService
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
            Event::dispatch(new FetchPhotosetPhotosCompletedEvent($this->task));
            return;
        }

        $this->task->update([
            'state_code' => States::STATE_RECURRING,
            'payload' => [
                'page' => $items->getNextPage()
            ]
        ]);

        Event::dispatch(new RecurredTaskEvent($this->task));

        self::dispatch($this->integration, $this->task, $items->getNextPage())
            ->onQueue(FlickrService::QUEUE_NAME);
    }
}
