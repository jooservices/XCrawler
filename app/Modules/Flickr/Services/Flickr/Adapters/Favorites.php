<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Flickr\Exceptions\InvalidRespondException;
use App\Modules\Flickr\Services\Flickr\DTO\FavoritesGetListDto;
use App\Modules\Flickr\Services\Flickr\Traits\HasList;
use GuzzleHttp\Exception\GuzzleException;

class Favorites extends BaseAdapter
{
    use HasList;

    public const PER_PAGE = 500;

    /**
     * @throws InvalidRespondException
     * @throws GuzzleException
     */
    public function getList(array $params = []): FavoritesGetListDto
    {
        return new FavoritesGetListDto(
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
