<?php

namespace App\Modules\Flickr\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Jobs\BaseJob;
use App\Modules\Flickr\Events\FetchContactsCompletedEvent;
use App\Modules\Flickr\Events\FetchContactsRecursiveEvent;
use App\Modules\Flickr\Exceptions\InvalidRespondException;
use App\Modules\Flickr\Services\Flickr\Adapters\Contacts;
use App\Modules\Flickr\Services\Flickr\Entities\ContactsListEntity;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

class ContactsJob extends BaseJob
{
    use SerializesModels;

    public $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Integration $integration, public int $page = 1)
    {
    }

    /**
     * Execute the job.
     *
     * @param FlickrService $flickrService
     * @return void
     * @throws GuzzleException
     * @throws InvalidRespondException
     */
    public function handle(FlickrService $flickrService): void
    {
        $service = app(FlickrContactService::class);
        $contactsService = $flickrService->setIntegration($this->integration)->contacts;
        /**
         * @var ContactsListEntity $contacts
         */
        $contacts = $contactsService->getList(['page' => $this->page]);

        $contacts->getItems()->each(function ($contact) use ($service) {
            $service->create($contact);
        });

        if ($contacts->isCompleted()) {
            Event::dispatch(new FetchContactsCompletedEvent());
            return;
        }

        Event::dispatch(new FetchContactsRecursiveEvent());

        self::dispatch($this->integration, $contacts->getNextPage())
            ->onQueue(FlickrService::QUEUE_NAME);
    }
}
