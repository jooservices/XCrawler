<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Core\Models\Task;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\PhotosetPhotoDownloadCompletedEvent;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

class DownloadPhoto extends BaseJob
{
    use SerializesModels;

    public $deleteWhenMissingModels = true;

    public function __construct(public Task $task)
    {
    }

    public function handle(IntegrationRepository $repository, FlickrService $service)
    {
        $integration = $repository->getNonPrimary(FlickrService::SERVICE_NAME);
        $adapter = $service->setIntegration($integration)->photos;

        $photo = $this->task->model;

        if (!$photo->sizes) {
            $sizes = $adapter->getSizes($photo->id);
            $photo->update([
                'sizes' => $sizes,
            ]);
        }

        $photo->refresh();
        $sizes = $photo->sizes;
        $size = end($sizes);

        $contents = file_get_contents($size['source']);
        $basenameWithoutParameters = explode(
            '?',
            pathinfo(
                $size['source'],
                PATHINFO_BASENAME
            )
        )[0];

        Storage::disk('local')->put($basenameWithoutParameters, $contents);

        $this->task->update(['state_code' => States::STATE_COMPLETED]);

        Event::dispatch(new PhotosetPhotoDownloadCompletedEvent($this->task));
    }
}
