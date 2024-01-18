<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Core\Entities\EntityInterface;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Services\Flickr\Entities\ContactsListEntity;
use GuzzleHttp\Exception\GuzzleException;

class Contacts extends BaseAdapter
{
    public const PER_PAGE = 1000;

    /**
     * @param array $params
     * @return EntityInterface
     * @throws FailedException
     * @throws GuzzleException
     * @throws InvalidRespondException
     * @throws MissingEntityElement
     */
    public function getList(array $params = []): EntityInterface
    {
        return new ContactsListEntity($this->request(
            'flickr.contacts.getList',
            array_merge(
                [
                    'per_page' => self::PER_PAGE,
                    'page' => 1,
                ],
                $params
            )
        ));
    }
}
