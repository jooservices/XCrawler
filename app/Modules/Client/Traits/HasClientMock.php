<?php

namespace App\Modules\Client\Traits;

use Exception;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

trait HasClientMock
{
    protected MockHandler $mocking;

    public function enableMocking(): self
    {
        $this->mocking = app(MockHandler::class);
        $this->reset();

        return $this;
    }

    /**
     * @phpstan-ignore-next-line
     *
     * @return $this
     *
     * @throws Exception
     */
    public function addMockResponse(
        int $status = 200,
        array $headers = [],
        mixed $body = null,
        string $version = '1.1',
        string $reason = null
    ): self {
        if (!isset($this->mocking)) {
            throw new Exception('Mocking is not enabled');
        }

        /*
         * @phpstan-ignore-next-line
         */
        $this->mocking->append(new Response($status, $headers, $body, $version, $reason));

        return $this;
    }

    public function addMockRequestException(
        string $message,
        RequestInterface $request,
        ResponseInterface $response = null,
        Throwable $previous = null
    ): self {
        if (!isset($this->mocking)) {
            throw new Exception('Mocking is not enabled');
        }

        $this->mocking->append(new RequestException($message, $request, $response, $previous));

        return $this;
    }
}
