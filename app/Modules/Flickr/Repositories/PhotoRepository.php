<?php

namespace App\Modules\Flickr\Repositories;

use App\Modules\Flickr\Models\FlickrPhoto;
use Illuminate\Support\Collection;

class PhotoRepository
{
    public function getNoSizesPhotos(int $limit = 10): Collection
    {
        return FlickrPhoto::whereNull('sizes')
            ->limit($limit)
            ->get();
    }
}
