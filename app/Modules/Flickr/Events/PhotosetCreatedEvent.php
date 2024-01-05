<?php

namespace App\Modules\Flickr\Events;

use App\Modules\Flickr\Models\FlickrPhotoset;
use Illuminate\Queue\SerializesModels;

class PhotosetCreatedEvent
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public FlickrPhotoset $photoset)
    {
    }
}
