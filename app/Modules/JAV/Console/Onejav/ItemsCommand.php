<?php

namespace App\Modules\JAV\Console\Onejav;

use App\Modules\JAV\Jobs\Onejav\ItemsJob;
use App\Modules\JAV\Services\OnejavService;
use Illuminate\Console\Command;

class ItemsCommand extends Command
{
    public const COMMAND = 'onejav:items {url}';

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
    protected $description = 'Crawling Onejav with specific URL.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        ItemsJob::dispatch($this->argument('url'))->onQueue(OnejavService::QUEUE_NAME);
    }
}
