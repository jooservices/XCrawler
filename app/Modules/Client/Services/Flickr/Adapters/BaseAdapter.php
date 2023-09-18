<?php

namespace App\Modules\Client\Services\Flickr\Adapters;

use App\Modules\Client\OAuth\OAuth1\Providers\Flickr;
use App\Modules\Client\OAuth\ProviderFactory;

class BaseAdapter implements AdapterInterface
{
    protected Flickr $provider;

    public function __construct()
    {
        $this->provider = app(ProviderFactory::class)->make(app(Flickr::class));
    }

    protected function isSuccessfull(array $response): bool
    {
        return $response['stat'] === 'ok';
    }
}
