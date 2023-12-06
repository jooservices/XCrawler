<?php

namespace App\Modules\Flickr\Services;

use App\Modules\Client\Services\FlickrManager;
use App\Modules\Flickr\Events\ContactCreatedEvent;
use App\Modules\Flickr\Jobs\ContactJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Repositories\ContactRepository;
use Illuminate\Support\Facades\Event;

class FlickrService
{
    public const TASK_CONTACT_FAVORITES = 'contact-favorites';
    public const TASK_CONTACT_PHOTOS = 'contact-photos';

    public const TASKS = [
        self::TASK_CONTACT_FAVORITES,
        self::TASK_CONTACT_PHOTOS,
    ];

    public function __construct(
        private readonly FlickrManager $flickrManager,
    ) {
    }

    public function contacts(int $page = 1): void
    {
        $contactsService = $this->flickrManager->contacts;
        $contactsService->getList(['page' => $page])->each(function ($contact) {
            $this->create($contact);
        });

        if ($page === $contactsService->totalPages()) {
            return;
        }

        ContactJob::dispatch($page + 1);
    }

    public function create(array $contact): FlickrContact
    {
        $contact = app(ContactRepository::class)->create($contact);

        if ($contact->wasRecentlyCreated) {
            Event::dispatch(new ContactCreatedEvent($contact));
        }

        return $contact;
    }
}
