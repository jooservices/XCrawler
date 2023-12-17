<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Flickr\Exceptions\InvalidRespondException;
use App\Modules\Flickr\Services\FlickrContactService;
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
    public function handle(FlickrService $flickrService, FlickrContactService $contactService): void
    {
        $flickrService->setIntegration($this->integration);
        $contactService = app(FlickrContactService::class);

        $adapter = $flickrService->favorites;
        $items = $adapter->getList([
            'user_id' => $this->nsid,
            'page' => $this->page
        ]);

        $contactService->addPhotos($items->getItems());

        if ($items->isCompleted()) {
            return;
        }

        self::dispatch($this->integration, $this->nsid, $items->getNextPage())
            ->onQueue(FlickrService::QUEUE_NAME);
    }
}
