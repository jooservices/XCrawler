<?php

namespace App\Modules\JAV\Console;

use App\Modules\JAV\Jobs\OnejavCrawlingItems;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class OnejavCrawling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onejav:crawling {url}';

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
