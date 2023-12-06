<?php

namespace App\Modules\Client\Tests;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\Models\RequestLog;
use App\Modules\Core\Models\Task;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Mockery\MockInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class TestCase extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->mockFlickr();

        Integration::truncate();
        RequestLog::truncate();

        Integration::factory()->create();

        Cache::delete('flickr_requests_count');
        Task::truncate();
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
            })
        );

        $this->mockFactory();
    }

    private function mockFlickrContacts(MockInterface &$mock)
    {
        for ($index = 1; $index <= 2; $index++) {
            $mock->shouldReceive('request')
                ->withArgs(function ($method, $url, $options) use ($index) {
                    return $method === 'POST'
                        && str_contains($url, 'flickr.contacts.getList')
                        && $options['form_params'] === [
                            'per_page' => 1000,
                            'page' => $index,
                        ];
                })
                ->andReturn(
                    new Response(
                        200,
                        [
                            'Content-Type' => 'application/json; charset=utf-8',
                        ],
                        $this->getFixtures('flickr_contacts_' . $index . '.json')
                    )
                );
        }

        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.contacts.getList')
                    && $options['form_params'] === [
                        'exception' => 'true',
                    ];
            })
            ->andThrow(new \Exception('Flickr error'));
    }

    public function getFixtures(string $path): string
    {
        return file_get_contents(__DIR__ . '/Fixtures/' . $path);
    }

    private function mockFlickrPeople(MockInterface &$mock)
    {
        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.people.getPhotos')
                    && $options['form_params']['per_page'] === 500
                    && $options['form_params']['user_id'] === '94529704@N02';
            })
            ->andReturn(
                new Response(
                    200,
                    [
                        'Content-Type' => 'application/json; charset=utf-8',
                    ],
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
                        [
                            'Content-Type' => 'application/json; charset=utf-8',
                        ],
                        $this->getFixtures('flickr_people_photos_' . $index . '.json')
                    )
                );
        }
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
                        && $options['form_params']['user_id'] === '94529704@N02';
                })
                ->andReturn(
                    new Response(
                        200,
                        [
                            'Content-Type' => 'application/json; charset=utf-8',
                        ],
                        $this->getFixtures('flickr_favorites_' . $index . '.json')
                    )
                );
        }
    }

    private function mockFlickrOauth(MockInterface &$mock)
    {
        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && $url === 'https://www.flickr.com/services/oauth/request_token';
            })
            ->andReturn(
                new Response(
                    200,
                    [
                    ],
                    $this->getFixtures('flickr_request_token')
                )
            );

        $mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && $url === 'https://www.flickr.com/services/oauth/access_token';
            })
            ->andReturn(
                new Response(
                    200,
                    [
                    ],
                    $this->getFixtures('flickr_access_token')
                )
            );
    }
}
