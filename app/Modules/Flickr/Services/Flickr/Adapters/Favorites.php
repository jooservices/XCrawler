<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Services\Flickr\Entities\FavoritesListEntity;
use App\Modules\Flickr\Services\Flickr\Traits\HasList;
use GuzzleHttp\Exception\GuzzleException;

class Favorites extends BaseAdapter
{
    use HasList;

    public const PER_PAGE = 500;

    /**
     * @param array $params
     * @return FavoritesListEntity
     * @throws GuzzleException
     * @throws InvalidRespondException
     * @throws FailedException
     * @throws MissingEntityElement
     */
    public function getList(array $params = []): FavoritesListEntity
    {
        return new FavoritesListEntity(
            $this->fetchList(
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
