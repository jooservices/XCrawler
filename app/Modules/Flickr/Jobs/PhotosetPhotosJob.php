<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Core\Models\Task;
use App\Modules\Core\StateMachine\Task\FailedState;
use App\Modules\Flickr\Events\FetchPhotosetPhotosCompletedEvent;
use App\Modules\Flickr\Jobs\Traits\HasRecurring;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

class PhotosetPhotosJob extends BaseJob
{
    use SerializesModels;
    use HasRecurring;

    public $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Integration $integration, public Task $task, public int $page = 1)
    {
    }

    /**
     * @param FlickrService $flickrService
     * @return void
     * @throws CouldNotPerformTransition
     */
    public function handle(FlickrService $flickrService): void
    {
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
            return;
        }

        $this->task->update([
            'payload' => [
                'page' => $items->getNextPage()
            ]
        ]);

        $this->recurringTask();

        self::dispatch($this->integration, $this->task, $items->getNextPage())
            ->onQueue(FlickrService::QUEUE_NAME);
    }

    public function failed(\Throwable $throwable): void
    {
        $this->task->transitionTo(FailedState::class);

        if ($throwable->getCode() === 1) {
            // Photoset not found
            $this->task->model->delete();

            return;
        }

        if ($throwable->getCode() === 2) {
            // User not found
            $this->task->model->contact->delete();
            $this->task->model->delete();
        }
    }
}
