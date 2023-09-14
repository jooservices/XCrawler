<?php

namespace App\Modules\JAV\Tests;

use App\Modules\Client\Services\Factory;
use GuzzleHttp\Client;
use Mockery;
use Mockery\MockInterface;

class TestCase extends \Tests\TestCase
{
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
    public function getFixtures(string $path)
    {
        return file_get_contents(__DIR__ . '/Fixtures/' . $path);
    }
}
