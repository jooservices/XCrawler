<?php

namespace App\Modules\JAV\Crawlers\Providers;

use App\Modules\Client\Responses\XResponseInterface;
use Illuminate\Support\Collection;

interface CrawlerProviderInterface
{
    public function crawl(string $url, array $payload = [], string $method = 'GET'): mixed;

    public function getItems(): Collection;

    public function getLastPage(): int;

    public function getResponse(): XResponseInterface;
}
