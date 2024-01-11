<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Flickr\Exceptions\PhotoHasNoSizesException;
use GuzzleHttp\Exception\GuzzleException;

class Photos extends BaseAdapter
{
    /**
     * @throws GuzzleException
     * @throws PhotoHasNoSizesException
     */
    public function getSizes(int $id): ?array
    {
        $response = $this->provider->request('flickr.photos.getSizes', [
            'photo_id' => $id
        ]);

        if (!$response->isSuccessful()) {
            return null;
        }

        if (!isset($response->getData()['sizes']['size'])) {
            throw new PhotoHasNoSizesException('Photo : ' . $id . ' has no sizes');
        }

        return $response->getData()['sizes']['size'];
    }
}
