<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseTaskJob;
use App\Modules\Core\Jobs\Traits\HasModelJob;
use App\Modules\Core\Models\Task;
use App\Modules\Flickr\Events\PhotosetCreatedEvent;
use App\Modules\Flickr\Exceptions\UserNotFoundException;
use App\Modules\Flickr\Jobs\Traits\HasRecurringTask;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Throwable;

class PhotosetsJob extends BaseTaskJob
{
    use SerializesModels;
    use HasRecurringTask;
    use HasModelJob;

    /**
     * @param Integration $integration
     * @param Task $task
     * @param int $page
     */
    public function __construct(public Integration $integration, public Task $task, public int $page = 1)
    {
    }

    public function process(): bool
    {
        $flickrService = app(FlickrService::class);
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
            // User not found
            case 1:
                $this->task->model->delete();
                $this->task->delete();

                throw new UserNotFoundException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }
}
