<?php

namespace App\Modules\JAV\Crawlers\Providers\Onejav;

use App\Modules\Client\Services\XClient;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Daily extends Items
{
    private Carbon $date;

    public function __construct(protected XClient $client)
    {
        parent::__construct($client);
        $this->date = Carbon::now();
    }

    public function setDay(Carbon $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDay(): Carbon
    {
        return $this->date;
    }

    public function getUrl(): string
    {
        return self::ONEJAV_URL . '/' . $this->date->format('Y/m/d');
    }

    public function crawl(string $url, array $data = [], string $method = 'GET'): Collection
    {
        $page = $data['page'] ?? 1;
        parent::crawl(
            $url,
            ['page' => $page],
            $method
        );

        if ($this->lastPage > $page) {
            $this->crawl($url, ['page' => $page + 1], $method);

            if (app()->environment('production')) {
                sleep(2);
            }
        }

        return $this->items;
    }
}
