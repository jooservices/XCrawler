<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;

class ContactPhotosJob extends BaseJob
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Integration $integration, public string $nsid, public int $page = 1)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FlickrService $flickrService)
    {
        $flickrService->setIntegration($this->integration);
        $contactService = app(FlickrContactService::class);

        $contact = FlickrContact::where('nsid', $this->nsid)->firstOrFail();
        $adapter = $flickrService->people;

        $photos = $adapter->getPhotos([
            'user_id' => $this->nsid,
            'page' => $this->page
        ]);

        $contactService->addPhotos($photos->getItems());

        if ($photos->isCompleted()) {
            return;
        }

        self::dispatch($this->integration, $this->nsid, $photos->getNextPage());
    }
}
