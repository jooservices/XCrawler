<?php

namespace App\Modules\Flickr\God\Providers;

use App\Modules\Core\God\Providers\ProviderInterface;
use GuzzleHttp\Psr7\Response;

class People extends AbstractProvider
{
    public const USER_DELETED_ID = 5;
    public const USER_UNKNOWN_ID = 2;

    public const INVALID_USER_IDS = [
        self::USER_DELETED_ID => 'User deleted',
        self::USER_UNKNOWN_ID => 'Unknown user',
    ];

    protected function getPhotos(): void
    {
        foreach (self::INVALID_USER_IDS as $userId => $message) {
            $this->mock->shouldReceive('request')
                ->withArgs(function ($method, $url, $options) use ($userId) {
                    return $method === 'POST'
                        && str_contains($url, 'flickr.people.getPhotos')
                        && $options['form_params']['user_id'] === (string)$userId;
                })
                ->andReturn(
                    new Response(
                        200,
                        self::DEFAULT_CONTENT_TYPE,
                        $this->error($userId, $message)
                    )
                );
        }

        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.people.getPhotos')
                    && $options['form_params']['user_id'] === self::NSID;
            })
            ->andReturn(
                $this->success('flickr_people_photos.json')
            );

        for ($index = 1; $index <= 2; $index++) {
            $this->mock->shouldReceive('request')
                ->withArgs(function ($method, $url, $options) use ($index) {
                    return $method === 'POST'
                        && str_contains($url, 'flickr.people.getPhotos')
                        && $options['form_params']['page'] === $index
                        && $options['form_params']['user_id'] === self::NSID_WITH_MULTI_PHOTOS_PAGES;
                })
                ->andReturn(
                    $this->success('flickr_people_photos_' . $index . '.json')
                );
        }
    }

    protected function getInfo(): void
    {
        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.people.getInfo')
                    && $options['form_params']['user_id'] === self::NSID;
            })
            ->andReturn(
                $this->success('flickr_people_getinfo.json')
            );

        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.people.getInfo')
                    && $options['form_params']['user_id'] === (string) self::USER_DELETED_ID;
            })
            ->andReturn(new Response(
                200,
                self::DEFAULT_CONTENT_TYPE,
                $this->error(5, self::INVALID_USER_IDS[self::USER_DELETED_ID])
            ));
    }

    public function factory(...$args): ProviderInterface
    {
        $this->getPhotos();
        $this->getInfo();

        return $this;
    }
}
