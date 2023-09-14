<?php

namespace App\Modules\JAV\Console\Onejav;

use App\Modules\JAV\Jobs\OnejavCrawlingItems;
use Illuminate\Console\Command;

class CrawlingItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onejav:crawling-items {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawling Onejav with specific URL.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        OnejavCrawlingItems::dispatch($this->argument('url'))->onQueue('onejav');

        return;
    }
}
