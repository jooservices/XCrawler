<?php

namespace App\Modules\Client\Traits;

use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

trait HasLogging
{
    protected LoggerInterface $logger;

    public function enableLogging(
        LoggerInterface $logger,
        string $format = MessageFormatter::SHORT,
        string $level = LogLevel::INFO
    ): self {
        $this->logger = $logger;

        /*
         * @phpstan-ignore-next-line
         */
        return $this->withMiddleware(Middleware::log($this->logger, new MessageFormatter($format), $level), 'log');
    }
}
