<?php

namespace App\Modules\JAV\Jobs;

use App\Modules\JAV\Crawlers\Providers\CrawlerManager;
use App\Modules\JAV\Crawlers\Providers\Onejav\Items;
use App\Modules\JAV\Events\OnejavCompleted;
use App\Modules\JAV\Repositories\Onejav;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Event;

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
    public function __construct(private string $url, private array $payload = [])
    {
    }

    public function uniqueId(): string
    {
        return md5(serialize([$this->url, $this->payload]));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $items = app(CrawlerManager::class)
            ->setProvider(app(Items::class))
            ->crawl($this->url, $this->payload, 'GET');

        $repository = app(Onejav::class);

        $items->each(function ($item) use ($repository) {
            $repository->firstOrCreate($item->getProperties());
        });

        Event::dispatch(new OnejavCompleted($items));
    }
}
