<?php

namespace App\Modules\JAV\Crawlers;

use App\Modules\Client\Responses\XResponse;
use App\Modules\Client\Responses\XResponseInterface;
use App\Modules\Client\Services\XClient;
use App\Modules\Core\Entity\EntityInterface;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;

abstract class AbstractProvider implements CrawlerProviderInterface
{
    protected Collection $items;

    protected XResponseInterface $response;

    public function __construct(protected XClient $client)
    {
        $this->items = collect();
    }

    public function crawl(string $url, array $payload = [], string $method = 'GET'): ?EntityInterface
    {
        $response = $this->client->{$method}($url, $payload);

        if (!$this->isSuccess($response)) {
            return null;
        }

        return $this->parse(new Crawler($response->getData()));
    }

    protected function isSuccess(?XResponse $response): bool
    {
        return $response !== null
            && $response->isSuccessful()
            && $response->getData() !== null;
    }

    abstract protected function parse(Crawler $crawler): EntityInterface;
}
