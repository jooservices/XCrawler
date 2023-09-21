<?php

namespace App\Modules\JAV\Services;

use App\Modules\Core\Facades\Setting;
use App\Modules\JAV\Crawlers\Providers\CrawlerManager;
use App\Modules\JAV\Crawlers\Providers\Onejav\Daily;
use App\Modules\JAV\Crawlers\Providers\Onejav\Items;
use App\Modules\JAV\Events\OnejavAllCompleted;
use App\Modules\JAV\Events\OnejavCompleted;
use App\Modules\JAV\Events\OnejavDailyCompleted;
use App\Modules\JAV\Events\OnejavRetried;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class OnejavService
{
    public function items(string $url, array $payload = []): Collection
    {
        $items = app(CrawlerManager::class)
            ->setProvider(app(Items::class))
            ->crawl($url, $payload);

        Event::dispatch(new OnejavCompleted($items));

        return $items;
    }

    public function daily(): Collection
    {
        $daily = app(Daily::class);
        $items = app(CrawlerManager::class)
            ->setProvider($daily)
            ->crawl($daily->getUrl());

        Event::dispatch(new OnejavCompleted($items));
        Event::dispatch(new OnejavDailyCompleted($daily->getDay(), $items));

        return $items;
    }

    public function new(): Collection
    {
        return $this->all();
    }

    public function all(string $prefix = 'new'): Collection
    {
        $slug = Str::slug($prefix);
        $currentPage = Setting::remember('onejav', $slug . '_current_page', fn () => 1);

        $service = app(CrawlerManager::class)->setProvider(app(Items::class));

        $items = $service->crawl(Items::ONEJAV_URL . '/' . $prefix, ['page' => $currentPage]);

        $lastPage = $service->getLastPage();
        $response = $service->getResponse();

        /**
         * Maybe server error by accident
         * - Try 3 times
         * - Move forward to next page
         */
        if (in_array($response->getStatusCode(), [404, 500])) {
            $retried = Setting::remember('onejav', $slug . '_retried', fn () => 0);
            $retried++;

            if ($retried <= 3) {
                Setting::setInt('onejav', $slug . '_retried', $retried);
                // Try next page
                $lastPage = $currentPage + 1;
                Event::dispatch(OnejavRetried::class);
            } else {
                //
                Setting::setInt('onejav', $slug . '_retried', 0);
            }
        }

        Setting::setInt('onejav', $slug . '_last_page', (int)$lastPage);

        // Reset back to 1
        if ($currentPage >= $lastPage) {
            Setting::setInt('onejav', $slug . '_current_page', 0);

            Event::dispatch(new OnejavAllCompleted());
        }

        Setting::increment('onejav', $slug . '_current_page');

        return $items;
    }

    public function popular(): Collection
    {
        return $this->all('popular');
    }
}
