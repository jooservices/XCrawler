<?php

namespace App\Modules\JAV\Crawlers\Providers;

use App\Modules\Client\Responses\XResponseInterface;

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

    public function getLastPage(): int
    {
        return $this->provider->getLastPage();
    }

    public function getResponse(): XResponseInterface
    {
        return $this->provider->getResponse();
    }
}
