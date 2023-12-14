<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Models\FlickrPhoto as FlickrPhotosModel;
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
        $contact = FlickrContact::where('nsid', $this->nsid)->firstOrFail();
        $adapter = $flickrService->people;
        $adapter->getList([
            'user_id' => $this->nsid,
            'page' => $this->page
        ])->each(function ($photo) use ($contact) {
            $contact->photos()->firstOrCreate(
                [
                    'owner' => $photo['owner'],
                    'id' => $photo['id']
                ],
                $photo
            );
        });

        if ($adapter->endOfList()) {
            return;
        }

        self::dispatch($this->integration, $this->nsid, $this->page + 1);
    }
}
