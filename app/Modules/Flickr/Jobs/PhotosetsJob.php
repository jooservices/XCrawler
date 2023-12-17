<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Flickr\Events\PhotosetCreatedEvent;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class PhotosetsJob extends BaseJob
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
    public function handle(FlickrService $flickrService): void
    {
        $flickrService->setIntegration($this->integration);
        $contact = FlickrContact::where('nsid', $this->nsid)->first();
        $adapter = $flickrService->photosets;
        $columns = DB::getSchemaBuilder()->getColumnListing('flickr_photosets');

        $items = $adapter->getList([
            'user_id' => $this->nsid,
            'page' => $this->page
        ]);

        $items->getItems()->each(function ($photoset) use ($columns, $contact) {
            $diff = array_diff(array_keys($photoset), $columns);
            foreach ($diff as $field) {
                unset($photoset[$field]);
            }

            $photoset['title'] = is_array($photoset['title']) ? $photoset['title']['_content'] : $photoset['title'];
            $photoset['description'] = is_array($photoset['description']) ? $photoset['description']['_content'] : $photoset['description'];

            $photoset = $contact->photosets()->updateOrCreate(
                [
                    'id' => $photoset['id'],
                    'owner' => $photoset['owner'],
                ],
                $photoset
            );

            if ($photoset->wasRecentlyCreated) {
                Event::dispatch(new PhotosetCreatedEvent($photoset));
            }
        });

        if ($items->isCompleted()) {
            return;
        }

        self::dispatch($this->integration, $this->nsid, $items->getNextPage())
            ->onQueue(FlickrService::QUEUE_NAME);
    }
}
