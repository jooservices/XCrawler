<?php

namespace App\Modules\Flickr\God\Providers;

use App\Modules\Core\God\Providers\ProviderInterface;
use App\Modules\Flickr\Database\factories\PhotoFactory;
use GuzzleHttp\Psr7\Response;

class Photos extends AbstractProvider
{
    protected function getSizes(): void
    {
        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photos.getSizes')
                    && $options['form_params']['photo_id'] === PhotoFactory::ID_WITH_SIZES;
            })
            ->andReturn(
                $this->success('flickr_photo_sizes.json')
            );

        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photos.getSizes')
                    && $options['form_params']['photo_id'] === 1;
            })
            ->andReturn(
                new Response(
                    200,
                    [
                        'Content-Type' => 'application/json; charset=utf-8',
                    ],
                    $this->error(1, 'Photo not found')
                )
            );

        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photos.getSizes')
                    && $options['form_params']['photo_id'] === -1;
            })
            ->andThrow($this->exception());

        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photos.getSizes')
                    && $options['form_params']['photo_id'] === -2;
            })
            ->andReturn(
                $this->success('flickr_photo_without_sizes.json')
            );

        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photos.getSizes')
                    && $options['form_params']['photo_id'] === 3;
            })
            ->andReturn(
                new Response(
                    200,
                    [
                        'Content-Type' => 'text/xml; charset=utf-8',
                    ],
                    '<?xml version="1.0" encoding="utf-8" ?>
<rsp stat="fail">
  <err code="2" msg="Permission denied" />
</rsp>'
                )
            );
    }

    public function factory(...$args): ProviderInterface
    {
        $this->getSizes();

        return $this;
    }
}
