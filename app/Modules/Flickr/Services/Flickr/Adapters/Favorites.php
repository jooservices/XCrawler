<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Exceptions\PermissionDeniedException;
use App\Modules\Flickr\Services\Flickr\Entities\FavoritesListEntity;
use GuzzleHttp\Exception\GuzzleException;

class Favorites extends BaseAdapter
{
    public const PER_PAGE = 500;

    public const ERROR_CODE_USER_NOT_FOUND = 1;

    /**
     * @param array $params
     * @return FavoritesListEntity
     * @throws FailedException
     * @throws GuzzleException
     * @throws InvalidRespondException
     * @throws MissingEntityElement
     * @throws PermissionDeniedException
     */
    public function getList(array $params = []): FavoritesListEntity
    {
        return new FavoritesListEntity(
            $this->request(
                'flickr.favorites.getList',
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
