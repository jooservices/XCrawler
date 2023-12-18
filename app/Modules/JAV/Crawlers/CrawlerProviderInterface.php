<?php

namespace App\Modules\JAV\Crawlers;

use App\Modules\Core\Entities\EntityInterface;

interface CrawlerProviderInterface
{
    public function crawl(string $url, array $payload = [], string $method = 'GET'): ?EntityInterface;
}
