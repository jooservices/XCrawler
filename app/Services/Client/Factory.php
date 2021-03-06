<?php

namespace App\Services\Client;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Cache;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\LaravelCacheStorage;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class Factory
{
    private array $options;
    private HandlerStack $handler;
    public array $history = [];

    public function __construct(private LoggerInterface $logger)
    {
        $this->reset();
    }

    /**
     * @link http://docs.guzzlephp.org/en/stable/request-options.html
     * @param array $options
     * @return Factory
     */
    public function withOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function make(): ClientInterface
    {
        $client = new Client(['handler' => $this->handler] + $this->options);
        $this->reset();

        return $client;
    }

    public function getHistory(ClientInterface $client): array
    {
        return $this->history[spl_object_id($client)] ?? [];
    }

    public function enableLogging(string $format = MessageFormatter::CLF): self
    {
        if ($this->logger === null) {
            throw new LogicException('In order to use logging a Logger instance must be provided to the Factory');
        }

        return $this->withMiddleware(
            Middleware::log($this->logger, new MessageFormatter($format)),
            'log'
        );
    }

    public function enableRetries(int $maxRetries = 3, int $delayInSec = 1, int $minErrorCode = 500): self
    {
        $decider = function ($retries, $_, $response) use ($maxRetries, $minErrorCode) {
            return $retries < $maxRetries
                && $response instanceof ResponseInterface
                && $response->getStatusCode() >= $minErrorCode;
        };

        $increasingDelay = fn($attempt) => $attempt * $delayInSec * 1000;

        return $this->withMiddleware(
            Middleware::retry($decider, $increasingDelay),
            'retry'
        );
    }

    public function enableCache()
    {
        return $this->withMiddleware(
            new CacheMiddleware(
                new PrivateCacheStrategy(
                    new LaravelCacheStorage(
                        Cache::driver()
                    )
                )
            ),
            'cache'
        );
    }

    public function withMiddleware(callable $middleware, string $name = ''): self
    {
        $this->handler->push($middleware, $name);

        return $this;
    }

    private function reset(): void
    {
        $this->options = [];
        $this->handler = HandlerStack::create();
    }
}
