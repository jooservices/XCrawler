<?php

namespace App\Modules\Client\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
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
    }

    private function mockFlickr(): void
    {
        $this->instance(
            Client::class,
            Mockery::mock(Client::class, function (MockInterface $mock) {
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

                $this->mockFlickrPeople($mock);

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
            })
        );

        $this->mockFactory();
    }

    public function getFixtures(string $path)
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

        for ($index = 1; $index <= 2; $index++) {
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
    }
}
