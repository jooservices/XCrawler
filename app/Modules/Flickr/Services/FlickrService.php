<?php

namespace App\Modules\Flickr\Services;

use App\Modules\Client\Services\FlickrManager;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\BeforeProcessContact;
use App\Modules\Flickr\Events\FlickrContactCreated;
use App\Modules\Flickr\Jobs\FlickrContacts;
use App\Modules\Flickr\Jobs\FlickrFavorites;
use App\Modules\Flickr\Jobs\FlickrPhotos;
use App\Modules\Flickr\Repositories\ContactRepository;
use Illuminate\Support\Facades\Event;

class FlickrService
{
    public function __construct(private FlickrManager $flickrManager)
    {
    }

    public function contactFavorites(): void
    {
        app(ContactRepository::class)->getContactForFavorites()->each(function ($contact) {
            Event::dispatch(new BeforeProcessContact($contact));

            /**
             * @var \App\Modules\Flickr\Models\FlickrContact $contact
             */
            $contact->update([
                'favorites_state_code' => States::STATE_IN_PROGRESS
            ]);

            FlickrFavorites::dispatch($contact->nsid)->onQueue('flickr');
        });
    }

    public function contactPhotos(): void
    {
        app(ContactRepository::class)->getContactsForPhotos()->each(function ($contact) {
            Event::dispatch(new BeforeProcessContact($contact));

            /**
             * @var \App\Modules\Flickr\Models\FlickrContact $contact
             */
            $contact->update([
                'state_code' => States::STATE_IN_PROGRESS
            ]);

            FlickrPhotos::dispatch($contact->nsid)->onQueue('flickr');
        });
    }

    public function contacts(int $page = 1)
    {
        $contactsService = $this->flickrManager->contacts;
        $contactsService->getList(['page' => $page])->each(function ($contact) {
            $this->createContact($contact);
        });

        if ($page === $contactsService->totalPages()) {
            return;
        }

        FlickrContacts::dispatch($page + 1);
    }

    public function createContact(array $contact)
    {
        $contact = app(ContactRepository::class)->create($contact);

        Event::dispatch(new FlickrContactCreated($contact));
    }
}
