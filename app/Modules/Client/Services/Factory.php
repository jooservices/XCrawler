<?php

namespace App\Modules\Client\Services;

use App\Modules\Client\Interfaces\FactoryInterface;
use App\Modules\Client\Interfaces\MockingInterface;
use App\Modules\Client\Traits\HasClientMock;
use App\Modules\Client\Traits\HasLogging;
use App\Modules\Client\Traits\HasOptions;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;

class Factory implements FactoryInterface, MockingInterface
{
    use HasClientMock;
    use HasLogging;
    use HasOptions;

    protected HandlerStack $handler;

    protected Client $client;

    /**
     * @phpstan-ignore-next-line
     */
    protected array $history = [];

    public function __construct()
    {
        $this->bootHasOptions();
        $this->reset();
    }

    public function reset(): self
    {
        $this->handler = HandlerStack::create($this->mocking ?? null);

        return $this;
    }

    public function enableRetries(int $maxRetries = 3, int $delayInSec = 1, int $minErrorCode = 500): self
    {
        $decider = function ($retries, $_, $response) use ($maxRetries, $minErrorCode) {
            return $retries < $maxRetries
                && $response instanceof ResponseInterface
                && $response->getStatusCode() >= $minErrorCode;
        };

        $increasingDelay = fn ($attempt) => $attempt * $delayInSec * 1000;

        return $this->withMiddleware(Middleware::retry($decider, $increasingDelay), 'retry');
    }

    public function withMiddleware(callable $middleware, string $name = ''): self
    {
        $this->handler->push($middleware, $name);

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getHistory(Client $client): array
    {
        return $this->history[spl_object_id($client)] ?? [];
    }

    public function make(): Client
    {
        $this->client = new Client(array_merge(['handler' => $this->handler], $this->getOptionsArray()));

        if (isset($this->mocking)) {
            /*
             * @link https://docs.guzzlephp.org/en/stable/testing.html#history-middleware
             */
            $this->history[$id = spl_object_id($this->client)] = [];
            $this->withMiddleware(Middleware::history($this->history[$id]), 'fake_history');
        }

        $this->reset();

        return $this->client;
    }
}
