<?php

namespace App\Modules\Flickr\Events;

use App\Modules\Flickr\Models\FlickrPhoto;

class PhotoSizedEvent
{
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public FlickrPhoto $photo)
    {
    }
}
