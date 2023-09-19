<?php

namespace Tests;

use App\Modules\Client\Services\Factory;
use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Mockery\MockInterface;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use WithFaker;
    use RefreshDatabase;

    protected function mockFactory()
    {
        $this->instance(Factory::class, Mockery::mock(Factory::class, function (MockInterface $mock) {
            $mock->shouldReceive('enableRetries')
                ->andReturnSelf();

            $mock->shouldReceive('make')
                ->andReturn(app(Client::class));

            $mock->shouldReceive('enableLogging');
        }));
    }
}
