<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Services\FlickrManager;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Flickr\Models\FlickrPhoto as FlickrPhotosModel;

class ContactPhotosJob extends BaseJob
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public string $nsid, public int $page = 1)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FlickrManager $flickrService)
    {
        $peopleService = $flickrService->people;
        $peopleService->getList([
            'user_id' => $this->nsid,
            'page' => $this->page
        ])->each(function ($photo) {
            FlickrPhotosModel::updateOrCreate(
                [
                    'owner' => $photo['owner'],
                    'id' => $photo['id']
                ],
                $photo
            );
        });

        if ($peopleService->endOfList()) {
            return;
        }

        self::dispatch($this->nsid, $this->page + 1);
    }
}
