<?php

namespace App\Modules\JAV\Tests\Unit\Crawlers;

use App\Modules\JAV\Crawlers\CrawlerManager;
use App\Modules\JAV\Crawlers\Providers\Onejav\ItemsProvider;
use App\Modules\JAV\Entities\Onejav\MoviesEntity;
use App\Modules\JAV\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\MockInterface;

class OnejavItemsTest extends TestCase
{
    public function testGetItems()
    {
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
                            file_get_contents(__DIR__ . '/../../Fixtures/onejav_2023_08_25_2.html'),
                        )
                    );
            })
        );

        $this->mockFactory();

        $items = app(CrawlerManager::class)
            ->setProvider(app(ItemsProvider::class))
            ->crawl($url, [], 'GET');

        $this->assertInstanceOf(MoviesEntity::class, $items);
        $this->assertCount(10, $items->items);

        $items->items->each(function ($item) {
            $this->assertEquals('2023-08-25', $item->date->format('Y-m-d'));
            $this->assertIsFloat($item->size);
            $this->assertTrue(filter_var($item->url, FILTER_VALIDATE_URL) !== false);
            $this->assertTrue(filter_var($item->cover, FILTER_VALIDATE_URL) !== false);
        });
    }
}
