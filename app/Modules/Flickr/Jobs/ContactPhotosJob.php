<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Core\Jobs\Traits\HasModelJob;
use App\Modules\Core\Jobs\Traits\HasTaskJob;
use App\Modules\Core\Models\Task;
use App\Modules\Core\StateMachine\Task\CompletedState;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Exceptions\UserDeletedException;
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
    use HasModelJob;
    use HasTaskJob;

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
        $contactService = app(FlickrContactService::class);

        $photos = $flickrService->setIntegration($this->integration)->people->getPhotos([
            'user_id' => $this->task->model->nsid,
            'page' => $this->page
        ]);

        $contactService->addPhotos($photos->getItems());

        if ($photos->isCompleted()) {
            return true;
        }

        $this->task->update([
            'payload' => [
                'page' => $photos->getNextPage()
            ]
        ]);

        $this->recurringTask();

        self::dispatch($this->integration, $this->task, $photos->getNextPage());

        return false;
    }

    /**
     * @throws UserDeletedException
     */
    protected function failedProcess(Throwable $throwable): void
    {
        switch ($throwable->getCode()) {
            // User deleted
            case 5:
                $this->task->model->delete();
                $this->task->delete();

                throw new UserDeletedException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }
}
