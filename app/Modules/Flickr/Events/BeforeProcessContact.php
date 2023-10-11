<?php

namespace App\Modules\Flickr\Events;

use App\Modules\Flickr\Models\FlickrContacts;
use Illuminate\Queue\SerializesModels;

class BeforeProcessContact
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public FlickrContacts $contact)
    {
    }
}
