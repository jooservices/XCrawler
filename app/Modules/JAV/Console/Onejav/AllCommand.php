<?php

namespace App\Modules\JAV\Console\Onejav;

use App\Modules\Core\Facades\Setting;
use App\Modules\JAV\Jobs\Onejav\AllJob;
use App\Modules\JAV\Services\OnejavService;
use Illuminate\Console\Command;

class AllCommand extends Command
{
    public const COMMAND = 'onejav:all {--all}';

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
    protected $description = 'Automate crawling Onejav.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $subpages = ['new', 'popular'];

        if ($this->option('all')) {
            $subpages = Setting::remember('onejav', 'subpages', fn() => ['new', 'popular']);
        }

        foreach ($subpages as $page) {
            $this->output->text("Crawling {$page} pages...");
            AllJob::dispatch($page)->onQueue(OnejavService::QUEUE_NAME);
        }
    }
}
