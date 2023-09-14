<?php

namespace App\Modules\JAV\Crawlers\Providers;

use Illuminate\Support\Collection;

interface CrawlerProviderInterface
{
    public function crawl(string $url, array $data = [], string $method = 'GET'): mixed;

    public function getItems(): Collection;

    public function getLastPage(): int;
}
