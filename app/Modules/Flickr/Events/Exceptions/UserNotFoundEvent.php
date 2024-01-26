<?php

namespace App\Modules\Flickr\Events\Exceptions;

use App\Modules\Flickr\Models\FlickrContact;

class UserNotFoundEvent
{
    public function __construct(public FlickrContact $contact)
    {
    }
}
