<?php

namespace App\Modules\JAV\Services;

use App\Modules\Core\Facades\Setting;
use App\Modules\JAV\Crawlers\Providers\CrawlerManager;
use App\Modules\JAV\Crawlers\Providers\Onejav\Daily;
use App\Modules\JAV\Crawlers\Providers\Onejav\Items;
use App\Modules\JAV\Events\OnejavAllCompleted;
use App\Modules\JAV\Events\OnejavCompleted;
use App\Modules\JAV\Events\OnejavDailyCompleted;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

class OnejavService
{
    public function items(string $url, array $payload = []): Collection
    {
        $items = app(CrawlerManager::class)
            ->setProvider(app(Items::class))
            ->crawl($url, $payload, 'GET');

        Event::dispatch(new OnejavCompleted($items));

        return $items;
    }

    public function daily(): Collection
    {
        $daily = app(Daily::class);
        $items = app(CrawlerManager::class)
            ->setProvider($daily)
            ->crawl($daily->getUrl(), [], 'GET');

        Event::dispatch(new OnejavCompleted($items));
        Event::dispatch(new OnejavDailyCompleted($daily->getDay(), $items));

        return $items;
    }

    public function new(): Collection
    {
        $currentPage = Setting::remember('onejav', 'new_current_page', fn () => 1);

        $service = app(CrawlerManager::class)->setProvider(app(Items::class));

        $items = $service->crawl(Items::ONEJAV_URL . '/new', [
            'query' => [
                'page' => $currentPage
            ]
        ], 'GET');

        $lastPage = $service->getLastPage();

        Setting::setInt('onejav', 'new_last_page', (int) $lastPage);

        // Reset back to 1
        if ($currentPage >= $lastPage) {
            Setting::setInt('onejav', 'new_current_page', 0);

            Event::dispatch(new OnejavAllCompleted());
        }

        Setting::increment('onejav', 'new_current_page');

        return $items;
    }
}
