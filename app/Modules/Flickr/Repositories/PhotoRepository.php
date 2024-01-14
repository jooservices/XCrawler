<?php

namespace App\Modules\Flickr\Repositories;

use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Models\FlickrPhoto;
use Illuminate\Support\Collection;

class PhotoRepository
{
    public function getNoSizesPhotos(int $limit = 10): Collection
    {
        $photos = FlickrPhoto::whereNull('sizes')
            ->limit($limit)
            ->get();

        FlickrPhoto::whereIn('id', $photos->pluck('id'))
            ->update([
                'state_code' => InProgressState::class
            ]);

        return $photos;
    }
}
