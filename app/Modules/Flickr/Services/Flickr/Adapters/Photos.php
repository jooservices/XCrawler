<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use GuzzleHttp\Exception\GuzzleException;

class Photos extends BaseAdapter
{
    /**
     * @param int $id
     * @return array|null
     * @throws GuzzleException
     * @throws InvalidRespondException
     * @throws FailedException
     * @throws MissingEntityElement
     */
    public function getSizes(int $id): ?array
    {
        $response = $this->request('flickr.photos.getSizes', [
            'photo_id' => $id
        ]);

        if (!isset($response['sizes']['size'])) {
            throw new MissingEntityElement(
                sprintf(
                    'Missing element "%s" in response',
                    'size'
                )
            );
        }

        return $response['sizes']['size'];
    }
}
