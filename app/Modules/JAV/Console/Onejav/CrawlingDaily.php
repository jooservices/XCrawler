<?php

namespace App\Modules\JAV\Console\Onejav;

use App\Modules\JAV\Jobs\OnejavCrawlingDaily;
use App\Modules\JAV\Services\OnejavService;
use Illuminate\Console\Command;

class CrawlingDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onejav:crawling-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawling Onejav daily.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        OnejavCrawlingDaily::dispatch()->onQueue(OnejavService::QUEUE_NAME);
    }
}
