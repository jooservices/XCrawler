<?php

namespace App\Modules\JAV\Tests\Unit\Services;

use App\Modules\Core\Entities\EntityInterface;
use App\Modules\Core\Facades\Setting;
use App\Modules\JAV\Events\Onejav\DailyCompletedEvent;
use App\Modules\JAV\Events\Onejav\ItemCreatedEvent;
use App\Modules\JAV\Events\Onejav\ItemsCompletedEvent;
use App\Modules\JAV\Events\Onejav\ItemUpdatedEvent;
use App\Modules\JAV\Events\Onejav\RetriedEvent;
use App\Modules\JAV\Exceptions\OnejavRetryFailed;
use App\Modules\JAV\Models\Onejav;
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
            ItemsCompletedEvent::class,
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

        Event::assertDispatched(ItemsCompletedEvent::class, function ($event) {
            return $event->items->count() === 10;
        });
    }

    public function testGetDaily()
    {
        Event::fake([
            DailyCompletedEvent::class,
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

        Event::assertDispatched(DailyCompletedEvent::class, function ($event) {
            return $event->date->format('Y-m-d') === Carbon::now()->format('Y-m-d')
                && $event->items->count() === 60;
        });
    }

    public function testGetAll()
    {
        Event::fake([
            DailyCompletedEvent::class,
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
            RetriedEvent::class,
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

        Setting::set(OnejavService::SERVICE_NAME, 'new_current_page', 12219);
        $this->service->all();

        $this->assertEquals(12220, Setting::get(OnejavService::SERVICE_NAME, 'new_current_page'));
        $this->assertEquals(12220, Setting::get(OnejavService::SERVICE_NAME, 'new_last_page'));
        $this->assertEquals(1, Setting::get(OnejavService::SERVICE_NAME, 'new_retried'));

        $this->service->all();
        $this->assertEquals(2, Setting::get(OnejavService::SERVICE_NAME, 'new_retried'));
        $this->assertEquals(12221, Setting::get(OnejavService::SERVICE_NAME, 'new_current_page'));
        $this->assertEquals(12221, Setting::get(OnejavService::SERVICE_NAME, 'new_last_page'));

        $this->service->all();
        $this->assertEquals(3, Setting::get(OnejavService::SERVICE_NAME, 'new_retried'));
        $this->assertEquals(12222, Setting::get(OnejavService::SERVICE_NAME, 'new_current_page'));
        $this->assertEquals(12222, Setting::get(OnejavService::SERVICE_NAME, 'new_last_page'));

        $this->expectException(OnejavRetryFailed::class);
        $this->service->all();
        $this->assertEquals(0, Setting::get(OnejavService::SERVICE_NAME, 'new_retried'));
        $this->assertEquals(1, Setting::get(OnejavService::SERVICE_NAME, 'new_current_page'));
        $this->assertEquals(1, Setting::get(OnejavService::SERVICE_NAME, 'new_last_page'));

        Event::assertDispatchedTimes(RetriedEvent::class, 3);
    }

    public function testCreate(): void
    {
        Event::fake([
            ItemCreatedEvent::class,
            ItemUpdatedEvent::class
        ]);

        $this->service = app(OnejavService::class);
        $movie = $this->service->create([
            'url' => $this->faker->url,
            'dvd_id' => $this->faker->uuid
        ]);

        $this->assertEmpty($movie->genrers);
        Event::assertDispatched(ItemCreatedEvent::class);

        $onejav = Onejav::where('dvd_id', $movie->dvd_id)->first();
        $this->service->create([
            'url' => $onejav->url,
            'dvd_id' => $onejav->dvd_id,
            'genres' => [
                $this->faker->name,
                $this->faker->name,
            ]
        ]);

        Event::assertDispatched(ItemUpdatedEvent::class);
        $this->assertCount(2, $movie->refresh()->genres);
    }

    public function testUpdate()
    {
        Event::fake([
            ItemCreatedEvent::class,
            ItemUpdatedEvent::class
        ]);

        $onejav = Onejav::factory()->create();
        $this->service = app(OnejavService::class);

        $this->service->create([
            'url' => $onejav->url,
            'dvd_id' => $onejav->dvd_id
        ]);

        Event::assertDispatched(ItemUpdatedEvent::class, function ($event) use ($onejav) {
            return $event->model->is($onejav);
        });
    }
}
