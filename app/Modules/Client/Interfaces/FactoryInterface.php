<?php

namespace App\Modules\Client\Interfaces;

use GuzzleHttp\Client;

interface FactoryInterface
{
    public function reset(): self;

    public function enableRetries(int $maxRetries = 3, int $delayInSec = 1, int $minErrorCode = 500): self;

    public function withMiddleware(callable $middleware, string $name = ''): self;

    public function getHistory(Client $client): array;

    public function make(): Client;
}
