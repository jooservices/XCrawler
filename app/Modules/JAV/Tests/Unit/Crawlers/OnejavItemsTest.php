<?php

namespace App\Modules\JAV\Tests\Unit\Crawlers;

use App\Modules\Client\Services\Factory;
use App\Modules\JAV\Crawlers\Providers\CrawlerManager;
use App\Modules\JAV\Crawlers\Providers\Onejav\Items;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

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

        $this->instance(Factory::class, Mockery::mock(Factory::class, function (MockInterface $mock) {
            $mock->shouldReceive('enableRetries')
                ->andReturnSelf();

            $mock->shouldReceive('make')
                ->andReturn(app(Client::class));

            $mock->shouldReceive('enableLogging');
        }));

        $response = app(CrawlerManager::class)
            ->setProvider(app(Items::class))
            ->crawl($url, [], 'GET');

        $this->assertInstanceOf(Collection::class, $response);
        $this->assertCount(10, $response);

        $response->each(function ($item) {
            $this->assertEquals('2023-08-25', $item->date->format('Y-m-d'));
            $this->assertIsFloat($item->size);
            $this->assertTrue(filter_var($item->url, FILTER_VALIDATE_URL) !== false);
            $this->assertTrue(filter_var($item->cover, FILTER_VALIDATE_URL) !== false);
        });
    }
}
