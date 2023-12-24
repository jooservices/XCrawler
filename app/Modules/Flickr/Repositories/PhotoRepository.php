<?php

namespace App\Modules\Flickr\Repositories;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Models\FlickrPhoto;
use Illuminate\Support\Collection;

class PhotoRepository
{
    public function getNoSizesPhotos(int $limit = 10): Collection
    {
        $photos = FlickrPhoto::whereNull('sizes')
            ->whereNull('state_code')
            ->limit($limit)
            ->get();

        FlickrPhoto::whereIn('id', $photos->pluck('id'))
            ->update([
                'state_code' => States::STATE_IN_PROGRESS
            ]);

        return $photos;
    }
}
