<?php

namespace App\Modules\JAV\Jobs;

use App\Modules\JAV\Crawlers\Providers\CrawlerManager;
use App\Modules\JAV\Crawlers\Providers\Onejav\Daily;
use App\Modules\JAV\Crawlers\Providers\Onejav\Items;
use App\Modules\JAV\Events\OnejavCompleted;
use App\Modules\JAV\Repositories\Onejav;
use App\Modules\JAV\Services\OnejavService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Spatie\SlackAlerts\Facades\SlackAlert;

class OnejavCrawlingDaily implements ShouldQueue
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
    public function __construct()
    {
    }

    public function uniqueId(): string
    {
        return Carbon::now()->format('Y/m/d');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $repository = app(Onejav::class);
        app(OnejavService::class)->daily()
            ->each(function ($item) use ($repository) {
                $repository->firstOrCreate($item->getProperties());
            });
    }
}
