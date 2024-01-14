<?php

namespace App\Modules\Flickr\Services\Flickr\Traits;

use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use GuzzleHttp\Exception\GuzzleException;

trait HasList
{
    /**
     * @throws InvalidRespondException
     * @throws GuzzleException
     * @throws FailedException
     */
    protected function fetchList(
        string $method,
        array $params,
    ): array {
        $response = $this->provider->request($method, $params);

        $data = $response->getData();

        if (!$data || is_string($data)) {
            throw new InvalidRespondException($response->getBody());
        }

        if (
            !$this->isSuccessfull($data)
        ) {
            throw new FailedException($data['message'] ?? 'Unknown error', $data['code'] ?? 0);
        }

        return $data;
    }
}
