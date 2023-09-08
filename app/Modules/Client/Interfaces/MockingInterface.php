<?php

namespace App\Modules\Client\Interfaces;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

interface MockingInterface
{
    public function enableMocking(): self;
    public function addMockResponse(
        int $status = 200,
        array $headers = [],
        mixed $body = null,
        string $version = '1.1',
        string $reason = null
    ): self;

    public function addMockRequestException(
        string $message,
        RequestInterface $request,
        ResponseInterface $response = null,
        Throwable $previous = null
    ): self;
}
