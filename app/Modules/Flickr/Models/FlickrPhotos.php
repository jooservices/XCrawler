<?php

namespace App\Modules\Flickr\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class FlickrPhotos extends Model
{
    protected $guarded = [];

    protected $connection = 'mongodb';

    protected $collection = 'flickr_photos';
}
