<?php

namespace App\Modules\JAV\Jobs;

use App\Modules\JAV\Crawlers\Providers\CrawlerManager;
use App\Modules\JAV\Crawlers\Providers\Onejav\OnejavItems;
use App\Modules\JAV\Repositories\Onejav;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class OnejavCrawlingItems implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private string $url)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = app(CrawlerManager::class)
            ->setProvider(app(OnejavItems::class))
            ->crawl($this->url, [], 'GET');

        $repository = app(Onejav::class);

        $response->each(function ($item) use ($repository) {
            $repository->firstOrCreate($item->getProperties());
        });
    }
}
