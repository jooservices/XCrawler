<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseTaskJob;
use App\Modules\Core\Jobs\Traits\HasModelJob;
use App\Modules\Core\Models\Task;
use App\Modules\Flickr\Events\Exceptions\PhotosetNotFoundEvent;
use App\Modules\Flickr\Events\FetchPhotosetPhotosCompletedEvent;
use App\Modules\Flickr\Jobs\Traits\HasRecurringTask;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Support\Facades\Event;
use Throwable;

class PhotosetPhotosJob extends BaseTaskJob
{
    use HasModelJob;
    use HasRecurringTask;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Integration $integration, public Task $task, public int $page = 1)
    {
    }

    public function process(): bool
    {
        $flickrService = app(FlickrService::class);
        $photoset = $this->task->model;

        $items = $flickrService->setIntegration($this->integration)
            ->photosets
            ->getPhotos([
                'user_id' => $photoset->owner,
                'photoset_id' => $photoset->id,
                'page' => $this->page
            ]);

        $items->getItems()->each(function ($photo) use ($photoset) {
            $photo = FlickrPhoto::updateOrCreate([
                'id' => $photo['id'],
                'owner' => $photoset->owner,
            ], $photo);

            $photoset->relationshipPhotos()->syncWithoutDetaching([$photo->id]);
        });

        if ($items->isCompleted()) {
            Event::dispatch(new FetchPhotosetPhotosCompletedEvent($this->task));
            return true;
        }

        $this->task->update([
            'payload' => [
                'page' => $items->getNextPage()
            ]
        ]);

        return $this->recurringTask($this->integration, $this->task, $items->getNextPage());
    }

    protected function failedProcess(Throwable $throwable): void
    {
        switch ($throwable->getCode()) {
            // Photoset not found
            case 1:
                Event::dispatch(new PhotosetNotFoundEvent($this->task->model));
                break;

            case 2:
                // User not found
                $contact = $this->task->model->contact;
                $this->task->model->delete();
                $contact->delete();
                $this->task->delete();
                break;
        }
    }
}
