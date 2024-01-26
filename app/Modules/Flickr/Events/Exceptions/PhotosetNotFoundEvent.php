<?php

namespace App\Modules\Flickr\Events\Exceptions;

use App\Modules\Flickr\Models\FlickrPhotoset;

class PhotosetNotFoundEvent
{
    public function __construct(public FlickrPhotoset $photoset)
    {
    }
}
