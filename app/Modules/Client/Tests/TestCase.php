<?php

namespace App\Modules\Client\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\MockInterface;

class TestCase extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->mockContacts();
    }

    private function mockContacts()
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

                // People photos
                $mock->shouldReceive('request')
                    ->withArgs(function ($method, $url, $options) use ($index) {
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
            })
        );

        $this->mockFactory();
    }

    public function getFixtures(string $path)
    {
        return file_get_contents(__DIR__ . '/Fixtures/' . $path);
    }
}
