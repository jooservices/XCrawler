<?php

namespace App\Modules\Flickr\God\Providers;

use App\Modules\Core\God\Providers\ProviderInterface;

class Oauth extends AbstractProvider
{
    public function factory(...$args): ProviderInterface
    {
        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url) {
                return $method === 'POST'
                    && $url === 'https://www.flickr.com/services/oauth/request_token';
            })
            ->andReturn(
                $this->success('flickr_request_token')
            );

        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url) {
                return $method === 'POST'
                    && $url === 'https://www.flickr.com/services/oauth/access_token';
            })
            ->andReturn(
                $this->success('flickr_access_token')
            );

        return $this;
    }
}
