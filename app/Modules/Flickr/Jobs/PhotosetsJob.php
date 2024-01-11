<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Core\Models\Task;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\PhotosetCreatedEvent;
use App\Modules\Flickr\Events\RecurredTaskEvent;
use App\Modules\Flickr\Exceptions\InvalidRespondException;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\FlickrService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class PhotosetsJob extends BaseJob
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
     * @return void
     * @throws InvalidRespondException
     * @throws GuzzleException
     */
    public function handle(FlickrService $flickrService): void
    {
        $flickrService->setIntegration($this->integration);
        $contact = $this->task->model;

        $adapter = $flickrService->photosets;
        $columns = DB::getSchemaBuilder()->getColumnListing('flickr_photosets');

        $items = $adapter->getList([
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
