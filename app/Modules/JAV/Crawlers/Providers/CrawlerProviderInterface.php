<?php

namespace App\Modules\JAV\Crawlers\Providers;

interface CrawlerProviderInterface
{
    public function crawl(string $url, array $data = []);
}
