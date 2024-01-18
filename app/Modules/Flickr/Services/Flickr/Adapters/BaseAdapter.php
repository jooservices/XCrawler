<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Client\OAuth\OAuth1\Providers\Flickr;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\PermissionDeniedException;
use GuzzleHttp\Exception\GuzzleException;

class BaseAdapter
{
    public function __construct(protected Flickr $provider)
    {
    }

    protected function isSuccessfull(array $response): bool
    {
        return $response['stat'] === 'ok';
    }

    /**
     * @throws GuzzleException
     * @throws InvalidRespondException|FailedException
     * @throws PermissionDeniedException
     */
    protected function request(string $method, array $params = []): array
    {
        $response = $this->provider->request($method, $params);
        $data = $response->getData();

        if (!$data || !is_array($data)) {
            if (is_string($response->getBody()) && str_contains($response->getBody(), 'Permission denied')) {
                throw new PermissionDeniedException($response->getBody(), 2);
            }

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
