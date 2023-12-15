<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Flickr\Exceptions\InvalidRespondException;
use App\Modules\Flickr\Services\Flickr\DTO\ContactsGetListDto;
use App\Modules\Flickr\Services\Flickr\Traits\HasList;
use GuzzleHttp\Exception\GuzzleException;

class Contacts extends BaseAdapter
{
    use HasList;

    public const PER_PAGE = 1000;

    /**
     * @throws InvalidRespondException
     * @throws GuzzleException
     */
    public function getList(array $params = []): ContactsGetListDto
    {
        return new ContactsGetListDto(
            $this->fetchList(
                'flickr.contacts.getList',
                array_merge(
                    [
                        'per_page' => self::PER_PAGE,
                        'page' => 1,
                        'filter' => 'both',
                        'sort' => 'name'
                    ],
                    $params
                )
            )
        );
    }
}
