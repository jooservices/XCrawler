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
                                $this->getFixtures(
                                    'onejav_new_' . $index . '.html',
                                ),
                            )
                        );
                }
            })
        );

        $this->mockFactory();

        Setting::setInt('onejav', 'new_current_page', 12215);

        OnejavCrawlingAll::dispatch('new');

        $this->assertDatabaseCount('onejav', 10, 'mongodb');
        // Move to next page
        $this->assertEquals(12216, Setting::getInt('onejav', 'new_current_page'));
    }
}
