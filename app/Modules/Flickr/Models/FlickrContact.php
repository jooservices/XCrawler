<?php

namespace App\Modules\Flickr\Models;

use App\Modules\Core\Models\Traits\HasStates;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $nsid
 */
class FlickrContact extends Model
{
    use HasStates;

    protected $guarded = [];

    protected $connection = 'mongodb';

    protected $collection = 'flickr_contacts';
}
