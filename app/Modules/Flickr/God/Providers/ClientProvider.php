<?php

namespace App\Modules\Flickr\God\Providers;

use App\Modules\Client\Services\Factory;
use App\Modules\Core\God\Providers\ProviderInterface;
use GuzzleHttp\Client;
use Mockery;
use Mockery\MockInterface;

class ClientProvider implements ProviderInterface
{
    private Mockery\MockInterface|Mockery\LegacyMockInterface $clientMock;

    public function __construct()
    {
        $this->clientMock = Mockery::mock(Client::class);
    }

    public function factory(...$args): ProviderInterface
    {
        foreach ($args[0] as $method) {
            $classNamespace = 'App\\Modules\\Flickr\\God\\Providers\\' . ucfirst($method);
            $this->clientMock = app($classNamespace)
                ->setMocking($this->clientMock)
                ->factory()
                ->getMocking();
        }

        app()->instance(Client::class, $this->clientMock);
        app()->instance(Factory::class, Mockery::mock(Factory::class, function (MockInterface|Mockery\LegacyMockInterface $mock) {
            /**
             * @phpstan-ignore-next-line
             */
            $mock->shouldReceive('enableRetries')
                ->andReturnSelf();

            $mock->shouldReceive('make')
                ->andReturn(app(Client::class));

            $mock->shouldReceive('enableLogging');
        }));

        return $this;
    }
}
