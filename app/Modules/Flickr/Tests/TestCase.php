<?php

namespace App\Modules\Flickr\Tests;

use App\Modules\Client\Tests\TestCase as BaseTestCase;
use App\Modules\Flickr\Database\factories\PhotoFactory;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\MockInterface;

class TestCase extends BaseTestCase
{
    private const NSID = '94529704@N02';
    private const PHOTOSET_ID = 72157674594210788;

    private const DEFAULT_CONTENT_TYPE = [
        'Content-Type' => 'application/json; charset=utf-8',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->mockFlickr();
    }

    private function mockFlickr(): void
    {
        $this->instance(
            Client::class,
            Mockery::mock(Client::class, function (MockInterface $mock) {
                $this->mockFlickrContacts($mock);
                $this->mockFlickrPeople($mock);
                $this->mockFlickrFavorites($mock);
                $this->mockFlickrOauth($mock);
                $this->mockFlickrPhotoSizes($mock);
                $this->mockFlickrPhotosets($mock);
            })
        );

        $this->mockFactory();
    }

    private function mockFlickrContacts(MockInterface &$mock): void
    {
        for ($index = 1; $index <= 2; $index++) {
            $mock->shouldReceive('request')
                ->withArgs(function ($method, $url, $options) use ($index) {
                    return $method === 'POST'
                        && str_contains($url, 'flickr.contacts.getList')
                        && $options['form_params']['per_page'] === 1000
                        && $options['form_params']['page'] === $index
                        && !isset($options['form_params']['exception']);
                })
                ->andReturn(
                    new Response(
                        200,
                        self::DEFAULT_CONTENT_TYPE,
                        $this->getFixtures('flickr_contacts_' . $index . '.json')
                    )
                );
        }

        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.contacts.getList')
                    && $options['form_params']['exception'] === true;
            })
            ->andThrow($this->exception());
    }

    private function mockFlickrPeople(MockInterface &$mock)
    {
        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.people.getPhotos')
                    && $options['form_params']['per_page'] === 500
                    && $options['form_params']['user_id'] === '-5';
            })
            ->andReturn(
                new Response(
                    200,
                    self::DEFAULT_CONTENT_TYPE,
                    json_encode([
                        'stat' => 'fail',
                        'code' => 5,
                    ])
                )
            );

        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.people.getPhotos')
                    && $options['form_params']['per_page'] === 500
                    && $options['form_params']['user_id'] === '44203036@N06';
            })
            ->andReturn(
                new Response(
                    200,
                    self::DEFAULT_CONTENT_TYPE,
                    $this->getFixtures('flickr_unknown.json')
                )
            );

        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.people.getPhotos')
                    && $options['form_params']['per_page'] === 500
                    && $options['form_params']['user_id'] === self::NSID;
            })
            ->andReturn(
                new Response(
                    200,
                    self::DEFAULT_CONTENT_TYPE,
                    $this->getFixtures('flickr_people_photos.json')
                )
            );

        // People photos
        for ($index = 1; $index <= 2; $index++) {
            $mock->shouldReceive('request')
                ->withArgs(function ($method, $url, $options) use ($index) {
                    return $method === 'POST'
                        && str_contains($url, 'flickr.people.getPhotos')
                        && $options['form_params']['per_page'] === 500
                        && $options['form_params']['page'] === $index
                        && $options['form_params']['user_id'] === '73115043@N07';
                })
                ->andReturn(
                    new Response(
                        200,
                        self::DEFAULT_CONTENT_TYPE,
                        $this->getFixtures('flickr_people_photos_' . $index . '.json')
                    )
                );
        }

        // Get info
        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.people.getInfo')
                    && $options['form_params']['user_id'] === '16842686@N04';
            })
            ->andReturn(
                new Response(
                    200,
                    self::DEFAULT_CONTENT_TYPE,
                    $this->getFixtures('flickr_people_getinfo.json')
                )
            );
        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.people.getInfo')
                    && $options['form_params']['user_id'] === 'exception';
            })
            ->andThrow($this->exception());

        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.people.getInfo')
                    && $options['form_params']['user_id'] === 'User deleted';
            })
            ->andReturn(new Response(
                200,
                self::DEFAULT_CONTENT_TYPE,
                '{
    "stat": "fail",
    "code": 5,
    "message": "User deleted"
}'
            ));
    }

    private function mockFlickrFavorites(MockInterface &$mock)
    {
        // People photos
        for ($index = 1; $index <= 4; $index++) {
            $mock->shouldReceive('request')
                ->withArgs(function ($method, $url, $options) use ($index) {
                    return $method === 'POST'
                        && str_contains($url, 'flickr.favorites.getList')
                        && $options['form_params']['per_page'] === 500
                        && $options['form_params']['page'] === $index
                        && $options['form_params']['user_id'] === self::NSID;
                })
                ->andReturn(
                    new Response(
                        200,
                        self::DEFAULT_CONTENT_TYPE,
                        $this->getFixtures('flickr_favorites_' . $index . '.json')
                    )
                );
        }

        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.favorites.getList')
                    && $options['form_params']['per_page'] === 500
                    && $options['form_params']['page'] === 1
                    && $options['form_params']['user_id'] === '64994773@N03'; // User not found
            })
            ->andReturn(
                new Response(
                    200,
                    self::DEFAULT_CONTENT_TYPE,
                    '{
    "stat": "fail",
    "code": 1,
    "message": "User not found"
}'
                )
            );
    }

    private function mockFlickrPhotoSizes(MockInterface &$mock)
    {
        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photos.getSizes')
                    && isset($options['form_params']['photo_id'])
                    && $options['form_params']['photo_id'] === PhotoFactory::ID_WITH_SIZES;
            })
            ->andReturn(
                new Response(
                    200,
                    [
                        'Content-Type' => 'application/json; charset=utf-8',
                    ],
                    $this->getFixtures('flickr_photo_sizes.json')
                )
            );

        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photos.getSizes')
                    && isset($options['form_params']['photo_id'])
                    && $options['form_params']['photo_id'] === 10;
            })
            ->andReturn(
                new Response(
                    200,
                    [
                        'Content-Type' => 'application/json; charset=utf-8',
                    ],
                    $this->getFixtures('flickr_photo_sizes_notfound.json')
                )
            );

        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photos.getSizes')
                    && $options['form_params']['photo_id'] === -1;
            })
            ->andThrow($this->exception());

        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photos.getSizes')
                    && $options['form_params']['photo_id'] === -2;
            })
            ->andReturn(
                new Response(
                    200,
                    [
                        'Content-Type' => 'application/json; charset=utf-8',
                    ],
                    $this->getFixtures('flickr_photo_without_sizes.json')
                )
            );
    }

    private function mockFlickrOauth(MockInterface &$mock)
    {
        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url) {
                return $method === 'POST'
                    && $url === 'https://www.flickr.com/services/oauth/request_token';
            })
            ->andReturn(
                new Response(
                    200,
                    self::DEFAULT_CONTENT_TYPE,
                    $this->getFixtures('flickr_request_token')
                )
            );

        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url) {
                return $method === 'POST'
                    && $url === 'https://www.flickr.com/services/oauth/access_token';
            })
            ->andReturn(
                new Response(
                    200,
                    self::DEFAULT_CONTENT_TYPE,
                    $this->getFixtures('flickr_access_token')
                )
            );
    }

    private function mockFlickrPhotosets(MockInterface &$mock)
    {
        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photosets.getList')
                    && $options['form_params']['per_page'] === 500
                    && $options['form_params']['user_id'] === '99097633@N00';
            })
            ->andReturn(
                new Response(
                    200,
                    [
                        'Content-Type' => 'application/json; charset=utf-8',
                    ],
                    $this->getFixtures('flickr_photosets.json')
                )
            );

        for ($index = 1; $index <= 2; $index++) {
            $mock->shouldReceive('request')
                ->withArgs(function ($method, $url, $options) use ($index) {
                    return $method === 'POST'
                        && str_contains($url, 'flickr.photosets.getList')
                        && $options['form_params']['page'] === $index
                        && $options['form_params']['user_id'] === '34938526@N02';
                })
                ->andReturn(
                    new Response(
                        200,
                        [
                            'Content-Type' => 'application/json; charset=utf-8',
                        ],
                        $this->getFixtures('flickr_photosets_' . $index . '.json')
                    )
                );
        }

        # Photosets
        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photosets.getPhotos')
                    && $options['form_params']['per_page'] === 500
                    && $options['form_params']['photoset_id'] === self::PHOTOSET_ID
                    && $options['form_params']['user_id'] === self::NSID;
            })
            ->andReturn(
                new Response(
                    200,
                    self::DEFAULT_CONTENT_TYPE,
                    $this->getFixtures('flickr_photosets_photos.json')
                )
            );
        $mock->shouldReceive('request')
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
                    json_encode([
                        'stat' => 'fail',
                        'code' => 1,
                    ])
                )
            );
        $mock->shouldReceive('request')
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
                    json_encode([
                        'stat' => 'fail',
                        'code' => 2,
                    ])
                )
            );

        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photosets.getInfo')
                    && $options['form_params']['photoset_id'] === self::PHOTOSET_ID;
            })
            ->andReturn(
                new Response(
                    200,
                    self::DEFAULT_CONTENT_TYPE,
                    $this->getFixtures('flickr_photosets_info.json')
                )
            );
        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.photosets.getInfo')
                    && $options['form_params']['photoset_id'] === -1;
            })
            ->andThrow($this->exception());
    }

    private function exception(): Exception
    {
        return new Exception('Flickr error');
    }
}
