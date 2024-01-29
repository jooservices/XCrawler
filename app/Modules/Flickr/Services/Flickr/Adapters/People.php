<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Exceptions\PermissionDeniedException;
use App\Modules\Flickr\Services\Flickr\Entities\PeopleInfoEntity;
use App\Modules\Flickr\Services\Flickr\Entities\PeoplePhotosEntity;
use GuzzleHttp\Exception\GuzzleException;

class People extends BaseAdapter
{
    public const PER_PAGE = 500;

    /**
     * @param array $params
     * @return PeoplePhotosEntity
     * @throws FailedException
     * @throws GuzzleException
     * @throws InvalidRespondException
     * @throws MissingEntityElement
     * @throws PermissionDeniedException
     */
    public function getPhotos(array $params): PeoplePhotosEntity
    {
        return new PeoplePhotosEntity(
            $this->request(
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

    /**
     * @param string $nsid
     * @return PeopleInfoEntity
     * @throws FailedException
     * @throws GuzzleException
     * @throws InvalidRespondException
     * @throws MissingEntityElement
     * @throws PermissionDeniedException
     */
    public function getInfo(string $nsid): PeopleInfoEntity
    {
        $response = $this->request('flickr.people.getInfo', ['user_id' => $nsid]);

        if (!isset($response['person'])) {
            throw new MissingEntityElement(
                sprintf(
                    'Missing element "%s" in response',
                    'person'
                )
            );
        }

        return new PeopleInfoEntity($response['person']);
    }
}
