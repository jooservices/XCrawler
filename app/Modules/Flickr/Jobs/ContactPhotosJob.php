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
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use Throwable;

class ContactPhotosJob extends BaseJob
{
    use SerializesModels;
    use HasRecurring;

    public bool $deleteWhenMissingModels = true;

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
     * @throws CouldNotPerformTransition
     */
    public function handle(FlickrService $flickrService): void
    {
        $contactService = app(FlickrContactService::class);

        $photos = $flickrService->setIntegration($this->integration)->people->getPhotos([
            'user_id' => $this->task->model->nsid,
            'page' => $this->page
        ]);

        $contactService->addPhotos($photos->getItems());

        if ($photos->isCompleted()) {
            $this->task->transitionTo(CompletedState::class);
            return;
        }

        $this->task->update([
            'payload' => [
                'page' => $photos->getNextPage()
            ]
        ]);

        $this->recurringTask();

        self::dispatch($this->integration, $this->task, $photos->getNextPage());
    }

    public function failed(Throwable $throwable)
    {
        // User deleted
        $this->task->transitionTo(FailedState::class);

        if ($throwable->getCode() === 5) {
            $this->task->model->delete();
            $this->task->delete();
        }
    }
}
