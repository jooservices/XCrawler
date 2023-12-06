<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Services\FlickrManager;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Flickr\Models\FlickrContact as FlickrContactsModel;
use App\Modules\Flickr\Models\FlickrPhoto as FlickrPhotosModel;
use App\Modules\Flickr\Services\FlickrService;

/**
 * Get all favorites of a contact.
 */
class ContactFavoritesJob extends BaseJob
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
    public function handle(FlickrManager $flickrManager, FlickrService $flickrService)
    {
        $adapter = $flickrManager->favorites;
        $adapter->getList([
            'user_id' => $this->nsid,
            'page' => $this->page
        ])->each(function ($photo) use ($flickrService) {
            unset($photo['date_faved']);
            $flickrService->create([
                'nsid' => $photo['owner']
            ]);
            /**
             * @TODO Use relationship
             */
            FlickrContactsModel::firstOrCreate([
                'nsid' => $photo['owner']
            ]);

            FlickrPhotosModel::updateOrCreate(
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

        self::dispatch($this->nsid, $this->page + 1);
    }
}
