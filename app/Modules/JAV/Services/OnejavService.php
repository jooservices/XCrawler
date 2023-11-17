<?php

namespace App\Modules\JAV\Services;

use App\Modules\Core\Facades\Setting;
use App\Modules\Core\Services\CRUD\AbstractCrudService;
use App\Modules\JAV\Crawlers\Providers\CrawlerManager;
use App\Modules\JAV\Crawlers\Providers\Onejav\Daily;
use App\Modules\JAV\Crawlers\Providers\Onejav\Items;
use App\Modules\JAV\Events\OnejavAllCompleted;
use App\Modules\JAV\Events\OnejavCompleted;
use App\Modules\JAV\Events\OnejavDailyCompleted;
use App\Modules\JAV\Events\OnejavRetried;
use App\Modules\JAV\Repositories\OnejavRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class OnejavService extends AbstractCrudService
{
    public const SERVICE_NAME = 'onejav';

    public function __construct(private CrawlerManager $service)
    {
    }

    protected function serviceName(): string
    {
        return self::SERVICE_NAME;
    }

    protected function getRepository(): OnejavRepository
    {
        return app(OnejavRepository::class);
    }

    public function items(string $url, array $payload = []): Collection
    {
        $items = $this->service
            ->setProvider(app(Items::class))
            ->crawl($url, $payload);

        Event::dispatch(new OnejavCompleted($items));

        return $items;
    }

    public function daily(): Collection
    {
        $daily = app(Daily::class);
        $items = $this->service
            ->setProvider($daily)
            ->crawl($daily->getUrl());

        Event::dispatch(new OnejavCompleted($items));
        Event::dispatch(new OnejavDailyCompleted($daily->getDay(), $items));

        return $items;
    }

    public function all(string $prefix = 'new'): Collection
    {
        $slug = Str::slug($prefix);
        $service = $this->service->setProvider(app(Items::class));

        $items = $service->crawl(
            Items::ONEJAV_URL . '/' . $prefix,
            ['page' => Setting::get('onejav', $slug . '_current_page', 1)],
        );
        $this->nextPage($slug);

        return $items;
    }

    private function nextPage(string $prefix = 'new')
    {
        $currentPage = Setting::get('onejav', $prefix . '_current_page', 1);
        $lastPage = $this->service->getLastPage();

        /**
         * Normally
         */
        if (!in_array($this->service->getResponse()->getStatusCode(), [404, 500])) {
            Setting::setInt('onejav', $prefix . '_last_page', $lastPage);
            if ($currentPage < $lastPage) {
                Setting::increment('onejav', $prefix . '_current_page');
                return;
            }

            // Reset back to first page
            Setting::setInt('onejav', $prefix . '_current_page', 1);
            Event::dispatch(new OnejavAllCompleted());

            return;
        }

        /**
         * Trying because sometimes the page have 404 or 500 error
         * So we will try 3 times with 3 next pages
         */
        $retried = Setting::get('onejav', $prefix . '_retried', 0);
        $retried = $retried < 3 ? $retried + 1 : 0;
        Setting::setInt('onejav', $prefix . '_retried', $retried);

        /**
         * We already tried 3 times
         */
        if ($retried === 0) {
            Setting::setInt('onejav', $prefix . '_current_page', 1);
            Setting::setInt('onejav', $prefix . '_last_page', 1);

            Event::dispatch(new OnejavAllCompleted());

            return;
        }

        /**
         * We will try next page
         */

        Setting::increment('onejav', $prefix . '_current_page');
        Setting::setInt('onejav', $prefix . '_last_page', $currentPage + 1);

        Event::dispatch(new OnejavRetried());
    }
}
