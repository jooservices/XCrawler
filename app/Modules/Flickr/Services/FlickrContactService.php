<?php

namespace App\Modules\Flickr\Services;

use App\Modules\Flickr\Events\ContactCreatedEvent;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Repositories\ContactRepository;
use Illuminate\Support\Facades\Event;

class FlickrContactService
{
    public function __construct(private readonly ContactRepository $repository)
    {
    }

    public function create(array $contact): FlickrContact
    {
        $contact = $this->repository->create($contact);

        if ($contact->wasRecentlyCreated) {
            Event::dispatch(new ContactCreatedEvent($contact));
        }

        return $contact;
    }
}
