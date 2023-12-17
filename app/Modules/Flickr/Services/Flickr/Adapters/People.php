<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Flickr\Services\Flickr\Entities\PeoplePhotosEntity;
use App\Modules\Flickr\Services\Flickr\Traits\HasList;

class People extends BaseAdapter
{
    use HasList;

    public const PER_PAGE = 500;

    public function getPhotos(array $params): PeoplePhotosEntity
    {
        return new PeoplePhotosEntity(
            $this->fetchList(
                'flickr.people.getPhotos',
                array_merge(
                    [
                        'per_page' => self::PER_PAGE,
                        'page' => 1,
                    ],
                    $params
                )
            )
        );
    }
}
