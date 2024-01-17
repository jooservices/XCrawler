<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Core\Models\Task;
use App\Modules\Core\StateMachine\Task\CompletedState;
use App\Modules\Core\StateMachine\Task\FailedState;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Jobs\Traits\HasRecurring;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Queue\SerializesModels;

/**
 * Get all favorites of a contact.
 */
class ContactFavoritesJob extends BaseJob
{
    use SerializesModels;
    use HasRecurring;

    public $deleteWhenMissingModels = true;

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
     * @param FlickrContactService $contactService
     * @return void
     * @throws GuzzleException
     * @throws InvalidRespondException
     * @throws MissingEntityElement|FailedException
     */
    public function handle(FlickrService $flickrService, FlickrContactService $contactService): void
    {
        $items = $flickrService->setIntegration($this->integration)->favorites->getList([
            'user_id' => $this->task->model->nsid,
            'page' => $this->page
        ]);

        $contactService->addPhotos($items->getItems());

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

    public function failed(\Throwable $exception)
    {
        if ($exception->getCode() === 1) {
            $this->task->transitionTo(FailedState::class);
            $this->task->model->tasks()->delete();
        }
    }
}
