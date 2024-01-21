<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Core\Jobs\Traits\HasModelJobs;
use App\Modules\Core\Models\Task;
use App\Modules\Core\StateMachine\Task\CompletedState;
use App\Modules\Core\StateMachine\Task\FailedState;
use App\Modules\Flickr\Events\PhotosetCreatedEvent;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Exceptions\UserNotFoundException;
use App\Modules\Flickr\Jobs\Traits\HasRecurring;
use App\Modules\Flickr\Services\FlickrService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Throwable;

class PhotosetsJob extends BaseJob
{
    use SerializesModels;
    use HasRecurring;
    use HasModelJobs;

    /**
     * @param Integration $integration
     * @param Task $task
     * @param int $page
     */
    public function __construct(public Integration $integration, public Task $task, public int $page = 1)
    {
    }

    /**
     * @param FlickrService $flickrService
     * @return void
     * @throws GuzzleException
     * @throws InvalidRespondException
     * @throws FailedException
     * @throws MissingEntityElement
     */
    public function handle(FlickrService $flickrService): void
    {
        $contact = $this->task->model;
        $columns = DB::getSchemaBuilder()->getColumnListing('flickr_photosets');

        $items = $flickrService->setIntegration($this->integration)->photosets->getList([
            'user_id' => $contact->nsid,
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
            $this->task->transitionTo(CompletedState::class);
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

    /**
     * @param Throwable $throwable
     * @return void
     * @throws UserNotFoundException
     */
    public function failed(Throwable $throwable): void
    {
        $this->task->transitionTo(FailedState::class);

        // User not found
        if ($throwable->getCode() === 1) {
            $this->task->model->delete();
            $this->task->delete();

            throw new UserNotFoundException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }
}
