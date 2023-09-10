<?php

namespace App\Modules\JAV\Tests\Unit\Crawlers;

use App\Modules\Client\Responses\XResponse;
use App\Modules\Client\Services\XClient;
use App\Modules\JAV\Crawlers\Providers\CrawlerManager;
use App\Modules\JAV\Crawlers\Providers\Onejav\OnejavItems;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;
use Tests\TestCase;

class OnejavItemsTest extends TestCase
{
    public function testGetItems()
    {
        $this->instance(
            XClient::class,
            \Mockery::mock(XClient::class, function ($mock) {
                $response = new XResponse();
                $response->setResponse(new Response(
                    200,
                    [],
                    file_get_contents(__DIR__ . '/../../Fixtures/onejav_2023_08_25_2.html'),
                ));

                $mock->shouldReceive('request')
                    ->once()
                    ->andReturn($response);
            })
        );

        $response = app(CrawlerManager::class)
            ->setProvider(app(OnejavItems::class))
            ->crawl($this->faker->url, [], 'GET');

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
