<?php

namespace App\Modules\JAV\Console\Onejav;

use App\Modules\JAV\Jobs\OnejavCrawlingAll;
use Illuminate\Console\Command;

class CrawlingAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onejav:crawling-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate crawling Onejav.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        OnejavCrawlingAll::dispatch()->onQueue('onejav');

        return;
    }
}
