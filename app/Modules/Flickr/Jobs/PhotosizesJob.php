<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Flickr\Events\PhotoSizedEvent;
use App\Modules\Flickr\Exceptions\PermissionDeniedException;
use App\Modules\Flickr\Exceptions\PhotoSizesNotFound;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Services\FlickrService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Throwable;

class PhotosizesJob extends BaseJob
{
    use SerializesModels;

    public $deleteWhenMissingModels = true;

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
     * @throws Exception
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

        $this->photo->update(['sizes' => $sizes,]);

        Event::dispatch(new PhotoSizedEvent($this->photo));
    }

    public function failed(Throwable $exception)
    {
        if ($exception instanceof PermissionDeniedException) {
            if ($this->integration->is_primary) {
                // Have no permission than just delete it
                $this->photo->delete();
                return;
            }

            $integration = app(IntegrationRepository::class)->getPrimary(FlickrService::SERVICE_NAME);
            self::dispatch($integration, $this->photo)->onQueue(FlickrService::QUEUE_NAME);
        }

        // Delete photo when it's not found
        if ($exception->getCode() === 1) {
            $this->photo->delete();
        }
    }
}
