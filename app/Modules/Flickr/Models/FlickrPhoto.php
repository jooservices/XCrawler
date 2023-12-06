<?php

namespace App\Modules\Flickr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jenssegers\Mongodb\Eloquent\Model;

class FlickrPhoto extends Model
{
    protected $guarded = [];

    protected $connection = 'mongodb';

    protected $collection = 'flickr_photos';

    public function contact(): BelongsTo
    {
        return $this->belongsTo(FlickrContact::class, 'owner', 'nsid');
    }
}
