<?php

namespace App\Modules\JAV\Crawlers\Providers;

class CrawlerManager
{
    private CrawlerProviderInterface $provider;

    public function setProvider(CrawlerProviderInterface $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function crawl(string $url, array $data = [], string $method = 'GET'): mixed
    {
        return $this->provider->crawl($url, $data, $method);
    }

    public function getItems(): mixed
    {
        return $this->provider->getItems();
    }
}
