<?php

namespace App\Modules\JAV\Services;

use App\Modules\Core\Facades\Setting;
use App\Modules\Core\Services\CRUD\AbstractCrudService;
use App\Modules\JAV\Crawlers\CrawlerManager;
use App\Modules\JAV\Crawlers\Providers\Onejav\Entities\ItemsEntity;
use App\Modules\JAV\Crawlers\Providers\Onejav\ItemsProvider;
use App\Modules\JAV\Events\Onejav\AllCompletedEvent;
use App\Modules\JAV\Events\Onejav\DailyCompletedEvent;
use App\Modules\JAV\Events\Onejav\ItemsCompletedEvent;
use App\Modules\JAV\Events\OnejavCompleted;
use App\Modules\JAV\Events\OnejavRetried;
use App\Modules\JAV\Repositories\OnejavRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class OnejavService extends AbstractCrudService
{
    public const SERVICE_NAME = 'onejav';
    public const QUEUE_NAME = 'onejav';
    public const DEFAULT_DATE_FORMAT = 'Y/m/d';
    public const ONEJAV_URL = 'https://onejav.com';

    public function __construct(private readonly CrawlerManager $service)
    {
        $this->service->setProvider(app(ItemsProvider::class));
    }

    protected function getRepository(): OnejavRepository
    {
        return app(OnejavRepository::class);
    }

    public function items(string $url, array $payload = []): ItemsEntity
    {
        /**
         * @var ItemsEntity $items
         */
        $items = $this->service->crawl($url, $payload);

        Event::dispatch(new ItemsCompletedEvent($items->items));

        return $items;
    }

    public function daily(int $page = 1): ItemsEntity
    {
        $today = Carbon::now();
        /**
         * @var ItemsEntity $items
         */
        $items = $this->service
            ->crawl(
                self::ONEJAV_URL . '/' . $today->format('Y/m/d'),
                ['page' => $page],
            );

        if ($page < $items->lastPage) {
            $items->items = $items->items->merge(self::daily($page + 1)->items);
        }

        Event::dispatch(new DailyCompletedEvent($today, $items->items));

        return $items;
    }

    public function all(string $prefix = 'new'): ?ItemsEntity
    {
        $slug = Str::slug($prefix);
        $currentPage = Setting::remember('onejav', $slug . '_current_page', fn() => 1);

        /**
         * @var ?ItemsEntity $items
         */
        $items = $this->service->crawl(
            self::ONEJAV_URL . '/' . $prefix,
            ['page' => $currentPage],
        );

        /**
         * All good. We will move to next page
         */
        if ($items) {
            Setting::setInt('onejav', $slug . '_last_page', $items->lastPage);
            if ($currentPage < $items->lastPage) {
                Setting::increment('onejav', $slug . '_current_page');
            } else {
                // Reset back to first page
                Setting::setInt('onejav', $slug . '_current_page', 1);
                Event::dispatch(new AllCompletedEvent());
            }

            return $items;
        }

        /**
         * Trying because sometimes the page have 404 or 500 error
         * So we will try 3 times with 3 next pages
         */
        $retried = Setting::get('onejav', $slug . '_retried', 0);
        $retried = $retried < 3 ? $retried + 1 : 0;
        Setting::setInt('onejav', $slug . '_retried', $retried);

        /**
         * We already tried 3 times
         */
        if ($retried === 0) {
            Setting::setInt('onejav', $slug . '_current_page', 1);
            Setting::setInt('onejav', $slug . '_last_page', 1);

            Event::dispatch(new AllCompletedEvent());

            return null;
        }

        /**
         * We will try next page
         */

        Setting::increment('onejav', $slug . '_current_page');
        Setting::setInt('onejav', $slug . '_last_page', $currentPage + 1);

        Event::dispatch(new OnejavRetried());

        return null;
    }
}
