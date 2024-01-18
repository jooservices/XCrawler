<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Services\Flickr\Entities\PhotosetEntity;
use App\Modules\Flickr\Services\Flickr\Entities\PhotosetPhotosEntity;
use App\Modules\Flickr\Services\Flickr\Entities\PhotosetsListEntity;
use GuzzleHttp\Exception\GuzzleException;

class Photosets extends BaseAdapter
{
    public const PER_PAGE = 500;

    /**
     * @param array $params
     * @return PhotosetsListEntity
     * @throws GuzzleException
     * @throws InvalidRespondException
     * @throws MissingEntityElement
     * @throws FailedException
     */
    public function getList(array $params = []): PhotosetsListEntity
    {
        return new PhotosetsListEntity(
            $this->request(
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

    /**
     * @param array $params
     * @return PhotosetPhotosEntity
     * @throws FailedException
     * @throws GuzzleException
     * @throws InvalidRespondException
     * @throws MissingEntityElement
     */
    public function getPhotos(array $params = []): PhotosetPhotosEntity
    {
        return new PhotosetPhotosEntity(
            $this->request(
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

    /**
     * @param int $photosetId
     * @return PhotosetEntity|null
     * @throws FailedException
     * @throws GuzzleException
     * @throws InvalidRespondException
     */
    public function getInfo(int $photosetId): ?PhotosetEntity
    {
        $response = $this->request('flickr.photosets.getInfo', [
            'photoset_id' => $photosetId
        ]);

        return new PhotosetEntity($response['photoset']);
    }
}
