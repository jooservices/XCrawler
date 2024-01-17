<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Flickr\Exceptions\PhotoHasNoSizesException;
use App\Modules\Flickr\Exceptions\PhotoNotFoundException;
use GuzzleHttp\Exception\GuzzleException;

class Photos extends BaseAdapter
{
    /**
     * @throws GuzzleException
     * @throws PhotoHasNoSizesException
     * @throws PhotoNotFoundException
     */
    public function getSizes(int $id): ?array
    {
        $response = $this->provider->request('flickr.photos.getSizes', [
            'photo_id' => $id
        ]);

        if (!$response->isSuccessful()) {
            return null;
        }

        if (!$this->isSuccessfull($response->getData()) && isset($response->getData()['code'])) {
            if ($response->getData()['code'] == 1) {
                throw new PhotoNotFoundException('Photo : ' . $id . ' not found', $response->getData()['code']);
            }
        }

        if (!isset($response->getData()['sizes']['size'])) {
            throw new PhotoHasNoSizesException('Photo : ' . $id . ' has no sizes');
        }

        return $response->getData()['sizes']['size'];
    }
}
