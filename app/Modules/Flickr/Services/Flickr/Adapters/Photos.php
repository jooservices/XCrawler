<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use GuzzleHttp\Exception\GuzzleException;

class Photos extends BaseAdapter
{
    /**
     * @throws GuzzleException
     */
    public function getSizes(int $id): ?array
    {
        $response = $this->provider->request('flickr.photos.getSizes', [
            'photo_id' => $id
        ]);

        if (!$response->isSuccessful()) {
            return null;
        }

        return $response->getData()['sizes']['size'];
    }
}
