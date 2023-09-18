<?php

namespace App\Modules\Flickr\Models;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $nsid
 */
class FlickrContacts extends Model
{
    protected $guarded = [];

    protected $connection = 'mongodb';

    protected $collection = 'flickr_contacts';
}
