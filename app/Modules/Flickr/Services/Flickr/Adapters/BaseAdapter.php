<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters;

use App\Modules\Client\OAuth\OAuth1\Providers\Flickr;

class BaseAdapter
{
    public function __construct(protected Flickr $provider)
    {
    }

    protected function isSuccessfull(array $response): bool
    {
        return $response['stat'] === 'ok';
    }
}
