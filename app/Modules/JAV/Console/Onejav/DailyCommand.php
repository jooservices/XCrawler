<?php

namespace App\Modules\JAV\Console\Onejav;

use App\Modules\JAV\Jobs\Onejav\DailyJob;
use App\Modules\JAV\Services\OnejavService;
use Illuminate\Console\Command;

class DailyCommand extends Command
{
    public const COMMAND = 'onejav:daily';

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
    protected $description = 'Crawling Onejav daily.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        DailyJob::dispatch()->onQueue(OnejavService::QUEUE_NAME);
    }
}
