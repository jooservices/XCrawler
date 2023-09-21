<?php

namespace App\Modules\JAV\Crawlers\Providers;

use App\Modules\Client\Responses\XResponse;
use App\Modules\Client\Responses\XResponseInterface;
use App\Modules\Client\Services\XClient;
use Illuminate\Support\Collection;

abstract class AbstractProvider implements CrawlerProviderInterface
{
    protected Collection $items;
    protected int $lastPage = 1;

    protected XResponseInterface $response;

    public function __construct(protected XClient $client)
    {
        $this->items = collect();
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function getLastPage(): int
    {
        return $this->lastPage;
    }

    public function getResponse(): XResponseInterface
    {
        return $this->response;
    }

    abstract protected function isSuccess(XResponse $response): bool;
}
