<?php

namespace App\Modules\JAV\Crawlers;

use App\Modules\Core\Entity\EntityInterface;

class CrawlerManager
{
    private CrawlerProviderInterface $provider;

    public function setProvider(CrawlerProviderInterface $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function crawl(string $url, array $data = [], string $method = 'GET'): ?EntityInterface
    {
        return $this->provider->crawl($url, $data, $method);
    }
}
