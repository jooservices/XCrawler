<?php

namespace Modules\Flick\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RefreshCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'flick:refresh {nsid? : The NSID of the user to refresh (default: authenticated user)}';

    protected $description = 'Refresh data for a specific user';

    public function handle()
    {
        $nsid = $this->argument('nsid') ?? 'me';

        $this->info("Scheduling refresh for: $nsid");

        // 1. Refresh Contacts
        \Modules\Flick\Models\FlickCrawlTask::create([
            'contact_nsid' => $nsid,
            'type' => 'FETCH_CONTACTS',
            'page' => 1,
            'status' => 'pending',
            'priority' => 50,
        ]);

        // 2. Refresh Photos
        // Note: If we refresh contacts, the recursion logic (if implemented in Manager) might trigger photo fetch.
        // But currently, my CallbackController does NOT trigger recursion automatically yet (I left comments).
        // So we should manually trigger photo fetch here too.

        \Modules\Flick\Models\FlickCrawlTask::create([
            'contact_nsid' => $nsid,
            'type' => 'FETCH_PHOTOS',
            'page' => 1,
            'status' => 'pending',
            'priority' => 50,
        ]);

        $this->info("Refresh tasks queued. Run 'flick:crawl' to process them if not running.");
    }
}
