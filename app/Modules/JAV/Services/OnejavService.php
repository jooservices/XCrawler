<?php

namespace App\Modules\JAV\Services;

use App\Modules\Core\Facades\Setting;
use App\Modules\JAV\Crawlers\Providers\CrawlerManager;
use App\Modules\JAV\Crawlers\Providers\Onejav\Daily;
use App\Modules\JAV\Crawlers\Providers\Onejav\Items;
use App\Modules\JAV\Events\OnejavAllCompleted;
use App\Modules\JAV\Events\OnejavCompleted;
use App\Modules\JAV\Events\OnejavDailyCompleted;
use Carbon\Carbon;
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

    public function all(): Collection
    {
        $currentPage = Setting::remember('onejav', 'last_page', fn () => 1);
        $service = app(CrawlerManager::class)->setProvider(app(Items::class));

        $items = $service->crawl(Items::ONEJAV_URL . '/new', ['page' => $currentPage], 'GET');
        $lastPage = $service->getLastPage();

        // Reset back to 1
        if ($currentPage >= $lastPage) {
            Setting::set('onejav', 'last_page', 0);

            Event::dispatch(new OnejavAllCompleted());
        }

        Setting::increment('onejav', 'last_page');

        return $items;
    }
}
