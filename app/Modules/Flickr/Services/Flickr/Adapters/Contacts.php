<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Core\Entities\EntityInterface;
use App\Modules\Flickr\Exceptions\InvalidRespondException;
use App\Modules\Flickr\Services\Flickr\Entities\ContactsListEntity;
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
    public function getList(array $params = []): EntityInterface
    {
        $data = $this->fetchList(
            'flickr.contacts.getList',
            array_merge(
                [
                    'per_page' => self::PER_PAGE,
                    'page' => 1,
                ],
                $params
            )
        );

        return new ContactsListEntity($data);
    }
}
