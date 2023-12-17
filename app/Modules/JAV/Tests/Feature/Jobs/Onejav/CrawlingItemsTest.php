<?php

namespace App\Modules\JAV\Tests\Feature\Jobs\Onejav;

use App\Modules\Client\Services\Factory;
use App\Modules\JAV\Events\OnejavItemCreated;
use App\Modules\JAV\Jobs\OnejavCrawlingItems;
use App\Modules\JAV\Models\Onejav;
use App\Modules\JAV\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;

class CrawlingItemsTest extends TestCase
{
    public function testHandle()
    {
        Event::fake(OnejavItemCreated::class);

        $url = 'https://onejav.com/2023/08/25?page=2';
        $this->instance(
            Client::class,
            Mockery::mock(Client::class, function (MockInterface $mock) use ($url) {
                $mock->shouldReceive('request')
                    ->withSomeOfArgs('GET', $url)
                    ->andReturn(
                        new Response(
                            200,
                            [],
                            $this->getFixtures('onejav_2023_08_25_2.html'),
                        )
                    );
            })
        );

        $this->instance(Factory::class, Mockery::mock(Factory::class, function (MockInterface $mock) {
            $mock->shouldReceive('enableRetries')
                ->andReturnSelf();

            $mock->shouldReceive('make')
                ->andReturn(app(Client::class));

            $mock->shouldReceive('enableLogging');
        }));

        OnejavCrawlingItems::dispatch($url);

        Event::assertDispatched(OnejavItemCreated::class, 10);
        $this->assertDatabaseCount('onejav', 10, 'mongodb');
    }
}
