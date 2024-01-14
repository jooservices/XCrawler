<?php

namespace App\Modules\Flickr\Events;

use App\Modules\Flickr\Models\FlickrPhotoset;

class PhotosetPhotoReadyForUploadEvent
{
    public function __construct(public FlickrPhotoset $photoset)
    {
    }
}
