<?php

namespace App\Modules\JAV\Tests\Unit\Services;

use App\Modules\Core\Entity\EntityInterface;
use App\Modules\Core\Facades\Setting;
use App\Modules\JAV\Events\OnejavCompleted;
use App\Modules\JAV\Events\OnejavDailyCompleted;
use App\Modules\JAV\Events\OnejavRetried;
use App\Modules\JAV\Services\OnejavService;
use App\Modules\JAV\Tests\TestCase;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class OnejavServiceTest extends TestCase
{
    private OnejavService $service;

    public function testGetItems()
    {
        Event::fake([
            OnejavCompleted::class,
        ]);

        $this->instance(
            Client::class,
            Mockery::mock(Client::class, function (MockInterface $mock) {
                $mock->shouldReceive('request')
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
        $this->service = app(OnejavService::class);

        $items = $this->service->items($this->faker->url);
        $this->assertInstanceOf(Collection::class, $items->items);
        $this->assertCount(10, $items->items);

        Event::assertDispatched(OnejavCompleted::class, function ($event) {
            return $event->items->count() === 10;
        });
    }

    public function testGetDaily()
    {
        Event::fake([
            OnejavCompleted::class,
            OnejavDailyCompleted::class,
        ]);

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
                                file_get_contents(__DIR__ . '/../../Fixtures/onejav_2023_09_05_' . $index . '.html'),
                            )
                        );
                }
            })
        );

        $this->mockFactory();
        $this->service = app(OnejavService::class);

        $items = $this->service->daily();

        $this->assertInstanceOf(EntityInterface::class, $items);
        $this->assertCount(60, $items->items);

        Event::assertDispatched(OnejavCompleted::class);
        Event::assertDispatched(OnejavDailyCompleted::class, function ($event) {
            return $event->date->format('Y-m-d') === Carbon::now()->format('Y-m-d')
                && $event->items->count() === 60;
        });
    }

    public function testGetAll()
    {
        Event::fake([
            OnejavCompleted::class,
            OnejavDailyCompleted::class,
        ]);

        $this->instance(
            Client::class,
            Mockery::mock(Client::class, function (MockInterface $mock) {
                for ($index = 12215; $index <= 12218; $index++) {
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
                                file_get_contents(__DIR__ . '/../../Fixtures/onejav_new_' . $index . '.html'),
                            )
                        );
                }
            })
        );

        $this->mockFactory();
        $this->service = app(OnejavService::class);

        Setting::set('onejav', 'new_current_page', 12215);
        $items = $this->service->all();

        $this->assertInstanceOf(EntityInterface::class, $items);
        $this->assertEquals(10, $items->items->count());
        $this->assertEquals(12216, Setting::get('onejav', 'new_current_page'));

        // 12216
        $this->service->all();
        $this->service->all();
        $this->service->all();

        $this->assertEquals(1, Setting::get('onejav', 'new_current_page'));
    }

    public function testGetAllWithException()
    {
        Event::fake([
            OnejavRetried::class,
        ]);

        $this->instance(
            Client::class,
            Mockery::mock(Client::class, function (MockInterface $mock) {
                for ($index = 12215; $index <= 12218; $index++) {
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
                                file_get_contents(__DIR__ . '/../../Fixtures/onejav_new_' . $index . '.html'),
                            )
                        );
                }
                $mock->shouldReceive('request')
                    ->withArgs(function ($method, $url, $payload) {
                        return $method === 'GET'
                            && !empty($url)
                            && in_array(
                                $payload['query']['page'],
                                [12219, 12220, 12221, 12222]
                            );
                    })
                    ->andReturn(
                        new Response(
                            404,
                            [],
                            ''
                        )
                    );
            })
        );

        $this->mockFactory();
        $this->service = app(OnejavService::class);

        Setting::set('onejav', 'new_current_page', 12219);
        $this->service->all();

        $this->assertEquals(12220, Setting::get('onejav', 'new_current_page'));
        $this->assertEquals(12220, Setting::get('onejav', 'new_last_page'));
        $this->assertEquals(1, Setting::get('onejav', 'new_retried'));

        $this->service->all();
        $this->assertEquals(2, Setting::get('onejav', 'new_retried'));
        $this->assertEquals(12221, Setting::get('onejav', 'new_current_page'));
        $this->assertEquals(12221, Setting::get('onejav', 'new_last_page'));

        $this->service->all();
        $this->assertEquals(3, Setting::get('onejav', 'new_retried'));
        $this->assertEquals(12222, Setting::get('onejav', 'new_current_page'));
        $this->assertEquals(12222, Setting::get('onejav', 'new_last_page'));

        $this->service->all();
        $this->assertEquals(0, Setting::get('onejav', 'new_retried'));
        $this->assertEquals(1, Setting::get('onejav', 'new_current_page'));
        $this->assertEquals(1, Setting::get('onejav', 'new_last_page'));

        Event::assertDispatchedTimes(OnejavRetried::class, 3);
    }
}
