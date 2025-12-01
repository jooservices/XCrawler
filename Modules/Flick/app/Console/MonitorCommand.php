<?php

namespace Modules\Flick\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'flick:monitor';

    protected $description = 'Check monitored contacts and queue updates if needed (Weekly)';

    public function handle()
    {
        $this->info("Checking monitored contacts...");

        // Find contacts monitored and not crawled in last 7 days (or never)
        $contacts = \Modules\Flick\Models\FlickContact::where('is_monitored', true)
            ->where(function ($query) {
                $query->whereNull('last_crawled_at')
                    ->orWhere('last_crawled_at', '<', now()->subDays(7));
            })
            ->get();

        $count = $contacts->count();
        $this->info("Found {$count} contacts needing update.");

        foreach ($contacts as $contact) {
            $this->info("Queuing update for: {$contact->nsid}");

            // Queue FETCH_PHOTOS
            \Modules\Flick\Models\FlickCrawlTask::create([
                'contact_nsid' => $contact->nsid,
                'type' => 'FETCH_PHOTOS',
                'page' => 1,
                'status' => 'pending',
                'priority' => 80, // High priority for monitored
            ]);

            // Note: We don't queue DOWNLOAD_PHOTOS here.
            // The CallbackController will detect 'is_monitored' and queue DOWNLOAD_PHOTOS automatically when FETCH_PHOTOS completes.
        }
    }
}
