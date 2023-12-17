<?php

namespace App\Modules\JAV\Crawlers;

use App\Modules\Core\Entity\EntityInterface;

interface CrawlerProviderInterface
{
    public function crawl(string $url, array $payload = [], string $method = 'GET'): ?EntityInterface;
}
