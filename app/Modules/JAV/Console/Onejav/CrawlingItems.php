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
    protected $description = 'Command description.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        OnejavCrawlingItems::dispatch($this->argument('url'));

        return;
    }
}
