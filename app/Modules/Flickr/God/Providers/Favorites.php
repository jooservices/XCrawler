<?php

namespace App\Modules\Flickr\God\Providers;

use App\Modules\Core\God\Providers\ProviderInterface;
use GuzzleHttp\Psr7\Response;

class Favorites extends AbstractProvider
{
    protected function getList(): void
    {
        for ($index = 1; $index <= 4; $index++) {
            $this->mock->shouldReceive('request')
                ->withArgs(function ($method, $url, $options) use ($index) {
                    return $method === 'POST'
                        && str_contains($url, 'flickr.favorites.getList')
                        && $options['form_params']['per_page'] === 500
                        && $options['form_params']['page'] === $index
                        && $options['form_params']['user_id'] === self::NSID;
                })
                ->andReturn(
                    $this->success('flickr_favorites_' . $index . '.json')
                );
        }

        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.favorites.getList')
                    && $options['form_params']['user_id'] === self::NSID_USER_NOT_FOUND; // User not found
            })
            ->andReturn(
                new Response(
                    200,
                    self::DEFAULT_CONTENT_TYPE,
                    $this->error(1, 'User not found')
                )
            );
    }

    public function factory(...$args): ProviderInterface
    {
        $this->getList();

        return $this;
    }
}
