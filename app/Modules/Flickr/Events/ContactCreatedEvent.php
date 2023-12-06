<?php

namespace App\Modules\Flickr\Events;

use App\Modules\Flickr\Models\FlickrContact;
use Illuminate\Queue\SerializesModels;

class ContactCreatedEvent
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public FlickrContact $contact)
    {
    }
}
