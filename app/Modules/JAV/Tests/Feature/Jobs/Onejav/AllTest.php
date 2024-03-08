<?php

namespace App\Modules\JAV\Tests\Feature\Jobs\Onejav;

use App\Modules\Core\Facades\Setting;
use App\Modules\JAV\Events\OnejavCompleted;
use App\Modules\JAV\Jobs\Onejav\AllJob;
use App\Modules\JAV\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\MockInterface;

class AllTest extends TestCase
{
    public function testHandle()
    {
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

        AllJob::dispatch('new');

        $this->assertDatabaseCount('onejav', 10);
        // Move to next page
        $this->assertEquals(12216, Setting::getInt('onejav', 'new_current_page'));
        $this->assertEquals(12218, Setting::getInt('onejav', 'new_last_page'));
    }

    public function testHaveNoItems()
    {
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
                                ''
                            )
                        );
                }
            })
        );

        $this->mockFactory();

        Setting::setInt('onejav', 'new_current_page', 12215);

        AllJob::dispatch('new');

        $this->assertDatabaseCount('onejav', 0);
        // Move to next page
        $this->assertEquals(1, Setting::getInt('onejav', 'new_current_page'));
        $this->assertEquals(1, Setting::getInt('onejav', 'new_last_page'));
    }
}
