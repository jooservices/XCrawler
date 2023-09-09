<?php

namespace App\Modules\JAV\Crawlers\Providers;

use App\Modules\Client\Responses\XResponse;
use App\Modules\Client\Services\XClient;

abstract class AbstractProvider implements CrawlerProviderInterface
{
    public function __construct(protected XClient $client)
    {
    }

    abstract protected function isSuccess(XResponse $response): bool;
}
