<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Core\Jobs\Traits\HasModelJob;
use App\Modules\Core\Jobs\Traits\HasTaskJob;
use App\Modules\Core\Models\Task;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Events\FetchPhotosetPhotosCompletedEvent;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Jobs\Traits\HasRecurring;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Services\FlickrService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Event;
use Throwable;

class PhotosetPhotosJob extends BaseJob
{
    use HasModelJob;
    use HasRecurring;
    use HasTaskJob;

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
     * @throws FailedException
     * @throws InvalidRespondException
     * @throws MissingEntityElement
     * @throws GuzzleException
     */
    public function handle(FlickrService $flickrService): void
    {
        $this->task->transitionTo(InProgressState::class);
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

    protected function failedProcess(Throwable $throwable): void
    {
        switch ($throwable->getCode()) {
            // Photoset deleted
            case 1:
                // Photoset not found
                $this->task->model->delete();
                break;

            case 2:
                // User not found
                $contact = $this->task->model->contact;
                $this->task->model->delete();
                $contact->delete();
                break;
        }
    }
}
