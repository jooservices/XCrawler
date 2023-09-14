<?php

namespace App\Modules\JAV\Tests\Unit\Crawlers;

use App\Modules\JAV\Crawlers\Providers\CrawlerManager;
use App\Modules\JAV\Crawlers\Providers\Onejav\Daily;
use App\Modules\JAV\Tests\TestCase;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;

class OnejavDailyTest extends TestCase
{
    public function test()
    {
        $this->instance(
            Client::class,
            Mockery::mock(Client::class, function (MockInterface $mock) {
                for ($index = 1; $index <= 6; $index++) {
                    $mock->shouldReceive('request')
                        ->withArgs(function ($method, $url, $payload) use ($index) {
                            return $method === 'GET'
                            && $url === 'https://onejav.com/2023/09/05'
                                && $payload['page'] === $index;
                        })
                        ->andReturn(
                            new Response(
                                200,
                                [],
                                file_get_contents(__DIR__ . '/../../Fixtures/onejav_2023_09_05_' . $index . '.html'),
                            )
                        );
                }
            })
        );

        $this->mockFactory();

        $daily = app(Daily::class);
        $daily->setDay(Carbon::create(2023, 9, 5));
        $items = app(CrawlerManager::class)
            ->setProvider($daily)
            ->crawl($daily->getUrl(), [], 'GET');

        $this->assertInstanceOf(Collection::class, $items);
        $this->assertCount(60, $items);
    }
}
