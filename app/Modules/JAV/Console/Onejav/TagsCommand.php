<?php

namespace App\Modules\JAV\Console\Onejav;

use App\Modules\Core\Facades\Setting;
use App\Modules\JAV\Crawlers\CrawlerManager;
use App\Modules\JAV\Crawlers\Providers\Onejav\TagsProvider;
use App\Modules\JAV\Jobs\Onejav\DailyJob;
use App\Modules\JAV\Services\OnejavService;
use Illuminate\Console\Command;

class TagsCommand extends Command
{
    public const COMMAND = 'onejav:tags';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all tags.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $url = 'https://onejav.com/tag/';

        $items = app(CrawlerManager::class)
            ->setProvider(app(TagsProvider::class))
            ->crawl($url, [], 'GET');

        if (!$items) {
            return;
        }

        $subpages = Setting::get(OnejavService::SERVICE_NAME, 'subpages', []);
        $subpages = array_merge($subpages, $items->items->pluck('url')->toArray());

        Setting::set(OnejavService::SERVICE_NAME, 'subpages', array_unique($subpages));

        $this->info('Total subpages: ' . count($subpages));
    }
}
