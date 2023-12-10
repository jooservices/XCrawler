<?php

namespace App\Modules\Flickr\Models;

use App\Modules\Core\Models\Traits\HasStates;
use App\Modules\Flickr\Database\factories\PhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jenssegers\Mongodb\Eloquent\Model;

class FlickrPhoto extends Model
{
    use HasStates;
    use HasFactory;

    protected $guarded = [];

    protected $connection = 'mongodb';

    protected $collection = 'flickr_photos';

    public function contact(): BelongsTo
    {
        return $this->belongsTo(FlickrContact::class, 'owner', 'nsid');
    }

    protected static function newFactory(): PhotoFactory
    {
        return PhotoFactory::new();
    }
}
