<?php

namespace Modules\Flick\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class StatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'flick:stats';

    protected $description = 'Show current statistics for Flick module';

    public function handle()
    {
        $this->info('Flick Module Statistics');
        $this->info('=======================');

        $contacts = \Modules\Flick\Models\FlickContact::count();
        $photos = \Modules\Flick\Models\FlickPhoto::count();
        $downloaded = \Modules\Flick\Models\FlickPhoto::where('is_downloaded', true)->count();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Contacts', $contacts],
                ['Total Photos', $photos],
                ['Downloaded Photos', $downloaded],
                ['Pending Downloads', $photos - $downloaded],
            ]
        );

        $this->newLine();
        $this->info('Queue Status');

        $pending = \Modules\Flick\Models\FlickCrawlTask::where('status', 'pending')->count();
        $queued = \Modules\Flick\Models\FlickCrawlTask::where('status', 'queued_at_hub')->count();
        $failed = \Modules\Flick\Models\FlickCrawlTask::where('status', 'failed')->count();
        $completed = \Modules\Flick\Models\FlickCrawlTask::where('status', 'completed')->count();

        $this->table(
            ['Status', 'Count'],
            [
                ['Pending', $pending],
                ['Queued at Hub', $queued],
                ['Failed', $failed],
                ['Completed', $completed],
            ]
        );
    }
}
