<?php

namespace App\Modules\JAV\Console;

use App\Modules\JAV\Jobs\Onejav\ItemJob;
use App\Modules\JAV\Models\Onejav;
use App\Modules\JAV\Services\OnejavService;
use Illuminate\Console\Command;

class MovieScanner extends Command
{
    public const COMMAND = 'jav:scan-movies {--source=}';

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
    protected $description = 'ReScan all movies from OneJAV';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $source = $this->option('source');

        switch ($source) {
            case 'onejav':
                foreach(Onejav::cursor() as $movie) {
                    ItemJob::dispatch($movie)->onQueue(OnejavService::QUEUE_NAME);
                    exit;
                }
                break;
            default:
                // Do nothing
                break;
        }
    }
}
