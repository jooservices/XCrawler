<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Services\FlickrService;

class PhotosetPhotosJob extends BaseJob
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Integration $integration, public int $photosetId, public int $page = 1)
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
        $photoset = FlickrPhotoset::where('id', $this->photosetId)->first();
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
            return;
        }

        self::dispatch($this->integration, $this->photosetId, $items->getNextPage())
            ->onQueue(FlickrService::QUEUE_NAME);
    }
}