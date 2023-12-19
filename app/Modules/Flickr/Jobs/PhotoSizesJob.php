<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\PhotoSizedEvent;
use App\Modules\Flickr\Exceptions\PhotoSizesNotFound;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Services\FlickrService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

class PhotoSizesJob extends BaseJob
{
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Integration $integration, public FlickrPhoto $photo)
    {
    }

    /**
     * @param FlickrService $flickrService
     * @return void
     * @throws GuzzleException
     * @throws \Exception
     */
    public function handle(FlickrService $flickrService): void
    {
        $flickrService->setIntegration($this->integration);
        $adapter = $flickrService->photos;

        $sizes = $adapter->getSizes($this->photo->id);

        if (!$sizes) {
            /**
             * @TODO Handle this case
             */
            throw new PhotoSizesNotFound('Sizes not found');
        }

        $this->photo->update([
            'sizes' => $sizes,
            'state_code' => States::STATE_COMPLETED
        ]);

        Event::dispatch(new PhotoSizedEvent($this->photo));
    }
}
