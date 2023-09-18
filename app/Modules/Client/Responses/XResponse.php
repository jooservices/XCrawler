<?php

namespace App\Modules\Client\Responses;

use Psr\Http\Message\ResponseInterface;

class XResponse implements XResponseInterface
{
    public bool $successful = false;

    private int $statusCode = 200;

    private array $headers = [];

    private ?string $body = null;

    private string $version = '1.1';

    private ?string $reason = null;

    private ResponseInterface $response;

    public function getResponse(): ?string
    {
        if (!$this->body) {
            return null;
        }

        $response = $this->body;

        if (!mb_detect_encoding($response, 'utf-8', true)) {
            $response = utf8_encode($response);
        }

        $response = iconv('UTF-8', 'UTF-8//IGNORE', (string)$response); // or
        $response = iconv('UTF-8', 'UTF-8//TRANSLIT', (string)$response); // or even
        $response = iconv('UTF-8', 'UTF-8//TRANSLIT//IGNORE', (string)$response); // not sure how this behaves

        return (string)$response;
    }

    public function setResponse(ResponseInterface $response): self
    {
        $this->response = $response;
        $this->successful = $this->isSuccessful();
        $this->statusCode = $this->response->getStatusCode();
        $this->headers = $this->response->getHeaders();
        $this->body = $this->response->getBody()->getContents();
        $this->version = $this->response->getProtocolVersion();
        $this->reason = $this->response->getReasonPhrase();

        return $this;
    }

    public function isSuccessful(): bool
    {
        return isset($this->response)
            && $this->response->getStatusCode() >= 200
            && $this->response->getStatusCode() < 300;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getData(): mixed
    {
        if (!isset($this->body)) {
            return null;
        }

        if (str_contains($this->headers['Content-Type'][0] ?? null, 'application/json') === true) {
            return json_decode($this->body, true);
        }

        return $this->body;
    }
}
