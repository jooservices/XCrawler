<?php

namespace App\Modules\JAV\Tests\Feature\Jobs\Onejav;

use App\Modules\Client\Services\Factory;
use App\Modules\JAV\Events\OnejavCompleted;
use App\Modules\JAV\Events\OnejavItemCreated;
use App\Modules\JAV\Events\OnejavItemUpdated;
use App\Modules\JAV\Jobs\OnejavCrawlingDaily;
use App\Modules\JAV\Models\Onejav;
use App\Modules\JAV\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;

class CrawlingDailyTest extends TestCase
{
    public function testHandle()
    {
        Onejav::truncate();
        Event::fake(
            [
                OnejavItemCreated::class,
                OnejavCompleted::class,
                OnejavItemUpdated::class,
            ]
        );

        $this->instance(
            Client::class,
            Mockery::mock(Client::class, function (MockInterface $mock) {
                for ($index = 1; $index <= 6; $index++) {
                    $mock->shouldReceive('request')
                        ->withArgs(function ($method, $url, $payload) use ($index) {
                            return $method === 'GET'
                                && !empty($url)
                                && $payload['query']['page'] === $index;
                        })
                        ->andReturn(
                            new Response(
                                200,
                                [],
                                $this->getFixtures('onejav_2023_09_05_' . $index . '.html'),
                            )
                        );
                }
            })
        );

        $this->instance(Factory::class, Mockery::mock(Factory::class, function (MockInterface $mock) {
            $mock->shouldReceive('enableRetries')
                ->andReturnSelf();

            $mock->shouldReceive('make')
                ->andReturn(app(Client::class));

            $mock->shouldReceive('enableLogging');
        }));

        Onejav::create([
            'url' => 'https://onejav.com/torrent/stars908',
            'dvd_id' => 'STARS-908',
            'cover' => $this->faker->imageUrl(),
            'performers' => [
                'Miyuu Yanagi',
            ],
            'genres' => [
                'Beautiful Girl',
                'Slender',
                'Featured Actress',
                'Creampie',
                'Hi-Def',
            ],
        ]);

        OnejavCrawlingDaily::dispatch();

        Event::assertDispatched(OnejavItemCreated::class, 59);
        Event::assertDispatched(OnejavCompleted::class);
        Event::assertDispatched(OnejavItemUpdated::class, function ($event) {
            return $event->model->url === 'https://onejav.com/torrent/stars908';
        });
        $this->assertDatabaseCount('onejav', 60, 'mongodb');
    }
}
