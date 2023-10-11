<?php

namespace App\Modules\Flickr\Services;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\BeforeProcessContact;
use App\Modules\Flickr\Jobs\FlickrFavorites;
use App\Modules\Flickr\Repositories\ContactRepository;
use Illuminate\Support\Facades\Event;

class FlickrService
{
    public function processContacts(): void
    {
        app(ContactRepository::class)->getContactForFavorites()->each(function ($contact) {
            Event::dispatch(new BeforeProcessContact($contact));

            /**
             * @var \App\Modules\Flickr\Models\FlickrContacts $contact
             */
            $contact->update([
                'favorites_state_code' => States::STATE_IN_PROGRESS
            ]);

            FlickrFavorites::dispatch($contact->nsid)->onQueue('flickr');
        });
    }
}
