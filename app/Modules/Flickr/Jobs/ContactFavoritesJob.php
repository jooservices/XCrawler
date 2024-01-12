<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Core\Models\Task;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\RecurredTaskEvent;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

/**
 * Get all favorites of a contact.
 */
class ContactFavoritesJob extends BaseJob
{
    use SerializesModels;

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
     * @throws MissingEntityElement
     */
    public function handle(FlickrService $flickrService, FlickrContactService $contactService): void
    {
        $flickrService->setIntegration($this->integration);

        try {
            $items = $flickrService->favorites->getList([
                'user_id' => $this->task->model->nsid,
                'page' => $this->page
            ]);
        } catch (FailedException $e) {
            if ($e->getCode() === 1) {
                // User not found
                $this->task->model->tasks()->delete();
            }
            return;
        }

        $contactService->addPhotos($items->getItems());

        if ($items->isCompleted()) {
            $this->task->updateState(States::STATE_COMPLETED);
            return;
        }

        $this->task->update([
            'state_code' => States::STATE_RECURRING,
            'payload' => [
                'page' => $items->getNextPage()
            ]
        ]);

        Event::dispatch(new RecurredTaskEvent($this->task));

        self::dispatch($this->integration, $this->task, $items->getNextPage())
            ->onQueue(FlickrService::QUEUE_NAME);
    }
}
