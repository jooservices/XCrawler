<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Flickr\Exceptions\InvalidRespondException;
use App\Modules\Flickr\Exceptions\MissingEntityElement;
use App\Modules\Flickr\Services\Flickr\Entities\PhotosetEntity;
use App\Modules\Flickr\Services\Flickr\Entities\PhotosetPhotosEntity;
use App\Modules\Flickr\Services\Flickr\Entities\PhotosetsListEntity;
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

    /**
     * @throws GuzzleException
     * @throws MissingEntityElement
     */
    public function getInfo(int $photosetId): ?PhotosetEntity
    {
        $response = $this->provider->request('flickr.photosets.getInfo', [
            'photoset_id' => $photosetId
        ]);

        if (!$response->isSuccessful() || !isset($response->getData()['photoset'])) {
            throw new MissingEntityElement('Can not get photoset info: ' . $photosetId);
        }

        return new PhotosetEntity($response->getData()['photoset']);
    }
}
