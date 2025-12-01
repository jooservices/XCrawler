<?php

namespace Modules\Flick\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class LikeCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'flick:like {nsid : The NSID of the contact} {--unlike : Remove from monitoring}';

    protected $description = 'Mark a contact as "liked" to be monitored weekly';

    public function handle()
    {
        $nsid = $this->argument('nsid');
        $unlike = $this->option('unlike');

        // Check if input is a URL
        if (filter_var($nsid, FILTER_VALIDATE_URL)) {
            $url = $nsid;
            $this->info("Input is a URL: $url");

            // Check DB
            $contact = \Modules\Flick\Models\FlickContact::where('profile_url', $url)->first();
            if ($contact) {
                $nsid = $contact->nsid;
                $this->info("Resolved from DB: $nsid");
            } else {
                // Queue resolution
                $this->info("Queueing resolution task...");
                \Modules\Flick\Models\FlickCrawlTask::create([
                    'contact_nsid' => 'pending_resolution_' . md5($url),
                    'type' => 'RESOLVE_USER',
                    'page' => 1,
                    'status' => 'pending',
                    'priority' => 100,
                    'payload' => ['url' => $url, 'original_action' => 'LIKE']
                ]);
                $this->info("Resolution task queued. Contact will be liked once resolved.");
                return;
            }
        }

        $contact = \Modules\Flick\Models\FlickContact::firstOrCreate(['nsid' => $nsid]);

        if ($unlike) {
            $contact->update(['is_monitored' => false]);
            $this->info("Contact {$nsid} is no longer monitored.");
        } else {
            $contact->update(['is_monitored' => true]);
            $this->info("Contact {$nsid} is now LIKED and will be monitored weekly.");

            // Optionally trigger an immediate check
            // $this->call('flick:refresh', ['nsid' => $nsid]);
        }
    }
}
