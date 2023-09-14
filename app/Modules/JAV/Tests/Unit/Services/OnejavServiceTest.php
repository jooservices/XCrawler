<?php

namespace App\Modules\JAV\Tests\Unit\Services;

use App\Modules\Core\Facades\Setting;
use App\Modules\JAV\Events\OnejavCompleted;
use App\Modules\JAV\Events\OnejavDailyCompleted;
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

class OnejavServiceTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();

        $this->service = app(OnejavService::class);
    }
    public function testGetItems()
    {
        Onejav::truncate();

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

        $items = $this->service->items($this->faker->url, []);
        $this->assertInstanceOf(Collection::class, $items);
        $this->assertCount(10, $items);
    }

    public function testGetDaily()
    {
        Onejav::truncate();
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

        $items = $this->service->daily();
        $this->assertInstanceOf(Collection::class, $items);
        $this->assertCount(60, $items);

        Event::assertDispatched(OnejavCompleted::class);
        Event::assertDispatched(OnejavDailyCompleted::class, function ($event) {
            return $event->date->format('Y-m-d') === Carbon::now()->format('Y-m-d')
                && $event->items->count() === 60;
        });
    }

    public function testGetAll()
    {
        Onejav::truncate();
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
                                && $payload['page'] === $index;
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

        Setting::set('onejav', 'last_page', 12215);
        $items = $this->service->all();

        $this->assertInstanceOf(Collection::class, $items);
        $this->assertEquals(10, $items->count());
        $this->assertEquals(12216, Setting::get('onejav', 'last_page'));

        // 12216
        $this->service->all();
        $this->service->all();
        $this->service->all();

        $this->assertEquals(1, Setting::get('onejav', 'last_page'));
    }
}
