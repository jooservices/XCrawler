<?php

namespace App\Modules\Flickr\Services\Flickr\Traits;

use App\Modules\Flickr\Exceptions\InvalidRespondException;
use GuzzleHttp\Exception\GuzzleException;

trait HasList
{
    /**
     * @throws InvalidRespondException
     * @throws GuzzleException
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
            return [];
        }

        return $data;
    }
}
