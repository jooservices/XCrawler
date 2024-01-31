<?php

namespace App\Modules\Flickr\God\Providers;

use App\Modules\Core\God\Providers\ProviderInterface;
use GuzzleHttp\Psr7\Response;

class Photosets extends AbstractProvider
{
    public function getList(): void
    {
        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photosets.getList')
                    && $options['form_params']['per_page'] === 500
                    && $options['form_params']['user_id'] === '99097633@N00';
            })
            ->andReturn(
                $this->success('flickr_photosets.json')
            );

        for ($index = 1; $index <= 2; $index++) {
            $this->mock->shouldReceive('request')
                ->withArgs(function ($method, $url, $options) use ($index) {
                    return $method === 'POST'
                        && str_contains($url, 'flickr.photosets.getList')
                        && $options['form_params']['page'] === $index
                        && $options['form_params']['user_id'] === '34938526@N02';
                })
                ->andReturn(
                    $this->success('flickr_photosets_' . $index . '.json')
                );
        }

        // User not found
        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photosets.getList')
                    && $options['form_params']['user_id'] === 'User not found';
            })
            ->andReturn(
                new Response(
                    200,
                    self::DEFAULT_CONTENT_TYPE,
                    $this->error(1, 'User not found')
                )
            );
    }

    protected function getPhotos()
    {
        // Photoset photos
        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photosets.getPhotos')
                    && $options['form_params']['per_page'] === 500
                    && $options['form_params']['photoset_id'] === self::PHOTOSET_ID
                    && $options['form_params']['user_id'] === self::NSID;
            })
            ->andReturn(
                $this->success('flickr_photosets_photos.json')
            );

        // Photoset not found
        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photosets.getPhotos')
                    && $options['form_params']['photoset_id'] === 1
                    && $options['form_params']['user_id'] === self::NSID;
            })
            ->andReturn(
                new Response(
                    200,
                    self::DEFAULT_CONTENT_TYPE,
                    $this->error(1, 'Photoset not found')
                )
            );

        // User not found
        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photosets.getPhotos')
                    && $options['form_params']['photoset_id'] === 2
                    && $options['form_params']['user_id'] === self::NSID;
            })
            ->andReturn(
                new Response(
                    200,
                    self::DEFAULT_CONTENT_TYPE,
                    $this->error(2, 'User not found')
                )
            );
    }

    protected function getInfo()
    {
        // Photoset info
        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photosets.getInfo')
                    && $options['form_params']['photoset_id'] === self::PHOTOSET_ID;
            })
            ->andReturn(
                $this->success('flickr_photosets_info.json')
            );

        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photosets.getInfo')
                    && $options['form_params']['photoset_id'] === -1;
            })
            ->andThrow($this->exception());
    }

    public function factory(...$args): ProviderInterface
    {
        $this->getList();
        $this->getPhotos();
        $this->getInfo();

        return $this;
    }
}
