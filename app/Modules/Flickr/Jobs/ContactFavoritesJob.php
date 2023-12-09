<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Flickr\Exceptions\InvalidRespondException;
use App\Modules\Flickr\Models\FlickrContact as FlickrContactsModel;
use App\Modules\Flickr\Models\FlickrPhoto as FlickrPhotosModel;
use App\Modules\Flickr\Services\FlickrService;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Get all favorites of a contact.
 */
class ContactFavoritesJob extends BaseJob
{
    /**
     * @param Integration $integration
     * @param string $nsid
     * @param int $page
     */
    public function __construct(public Integration $integration, public string $nsid, public int $page = 1)
    {
    }

    /**
     * @throws GuzzleException
     * @throws InvalidRespondException
     */
    public function handle(FlickrService $flickrService): void
    {
        $flickrService->setIntegration($this->integration);
        $adapter = $flickrService->favorites;
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

        self::dispatch($this->integration, $this->nsid, $this->page + 1)->onQueue(FlickrService::QUEUE_NAME);
    }
}
