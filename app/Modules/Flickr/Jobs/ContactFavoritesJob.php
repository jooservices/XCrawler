<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseTaskJob;
use App\Modules\Core\Jobs\Traits\HasModelJob;
use App\Modules\Core\Models\Task;
use App\Modules\Flickr\Events\Exceptions\UserNotFoundEvent;
use App\Modules\Flickr\Exceptions\UserNotFoundException;
use App\Modules\Flickr\Jobs\Traits\HasRecurringTask;
use App\Modules\Flickr\Services\Flickr\Adapters\Favorites;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Throwable;

/**
 * Get all favorites of a contact.
 */
class ContactFavoritesJob extends BaseTaskJob
{
    use SerializesModels;
    use HasRecurringTask;
    use HasModelJob;

    /**
     * @param Integration $integration
     * @param Task $task
     * @param int $page
     */
    public function __construct(
        public Integration $integration,
        public Task $task,
        public int $page = 1
    ) {
    }

    public function process(): bool
    {
        $flickrService = app(FlickrService::class);
        $contactService = app(FlickrContactService::class);

        $items = $flickrService->setIntegration($this->integration)->favorites->getList([
            'user_id' => $this->task->model->nsid,
            'page' => $this->page
        ]);

        $contactService->addPhotos($items->getItems());

        if ($items->isCompleted()) {
            return true;
        }

        $this->task->update([
            'payload' => [
                'page' => $items->getNextPage()
            ]
        ]);

        return $this->recurringTask(
            $this->integration,
            $this->task,
            $items->getNextPage()
        );
    }

    /**
     * @throws UserNotFoundException
     */
    protected function failedProcess(Throwable $throwable): void
    {
        switch ($throwable->getCode()) {
            case Favorites::ERROR_CODE_USER_NOT_FOUND:
                Event::dispatch(new UserNotFoundEvent($this->task->model));
                throw new UserNotFoundException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }
}
