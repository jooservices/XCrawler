<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Services\Flickr\Entities\PeopleInfoEntity;
use App\Modules\Flickr\Services\Flickr\Entities\PeoplePhotosEntity;
use App\Modules\Flickr\Services\Flickr\Traits\HasList;
use GuzzleHttp\Exception\GuzzleException;

class People extends BaseAdapter
{
    use HasList;

    public const PER_PAGE = 500;

    /**
     * @param array $params
     * @return PeoplePhotosEntity
     * @throws MissingEntityElement
     * @throws FailedException
     * @throws InvalidRespondException
     * @throws GuzzleException
     */
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

    public function getInfo(string $nsid): PeopleInfoEntity
    {
        $response = $this->provider->request('flickr.people.getInfo', ['user_id' => $nsid]);

        if (!$response->isSuccessful() || !isset($response->getData()['person'])) {
            throw new MissingEntityElement('Can not get person info: ' . $nsid);
        }

        return new PeopleInfoEntity($response->getData()['person']);
    }
}
