<?php

namespace App\Modules\JAV\Tests\Feature\Jobs\Onejav;

use App\Modules\Core\Facades\Setting;
use App\Modules\JAV\Events\OnejavCompleted;
use App\Modules\JAV\Events\OnejavDailyCompleted;
use App\Modules\JAV\Jobs\OnejavCrawlingAll;
use App\Modules\JAV\Models\Onejav;
use App\Modules\JAV\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;

class CrawlingAllTest extends TestCase
{
    public function testHandle()
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
                                $this->getFixtures(
                                    'onejav_new_' . $index . '.html',
                                ),
                            )
                        );
                }
            })
        );

        $this->mockFactory();
        Setting::set('onejav', 'last_page', 12215);

        OnejavCrawlingAll::dispatch();

        $this->assertDatabaseCount('onejav', 10, 'mongodb');
        $this->assertEquals(12216, Setting::get('onejav', 'last_page'));
    }
}
