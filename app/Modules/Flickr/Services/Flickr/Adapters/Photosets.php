<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Flickr\Exceptions\InvalidRespondException;
use App\Modules\Flickr\Services\Flickr\Entities\PhotosetsListEntity;
use App\Modules\Flickr\Services\Flickr\Entities\PhotosetPhotosEntity;
use App\Modules\Flickr\Services\Flickr\Traits\HasList;
use GuzzleHttp\Exception\GuzzleException;

class Photosets extends BaseAdapter
{
    use HasList;

    public const PER_PAGE = 500;

    /**
     * @throws InvalidRespondException
     * @throws GuzzleException
     */
    public function getList(array $params = []): PhotosetsListEntity
    {
        return new PhotosetsListEntity(
            $this->fetchList(
                'flickr.photosets.getList',
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

    public function getPhotos(array $params = []): PhotosetPhotosEntity
    {
        return new PhotosetPhotosEntity(
            $this->fetchList(
                'flickr.photosets.getPhotos',
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
