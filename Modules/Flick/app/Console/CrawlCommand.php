<?php

namespace Modules\Flick\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CrawlCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'flick:crawl {nsid? : The NSID of the user to crawl (default: authenticated user)}';

    protected $description = 'Start crawling Flickr contacts and photos recursively';

    protected $shouldExit = false;

    public function handle()
    {
        $this->trap([SIGTERM, SIGINT], function () {
            $this->info("Signal received. Finishing current task and exiting...");
            $this->shouldExit = true;
        });

        $nsid = $this->argument('nsid');

        // Check if input is a URL
        if ($nsid && filter_var($nsid, FILTER_VALIDATE_URL)) {
            $url = $nsid;
            $this->info("Input is a URL: $url");

            // Check if we already resolved this URL
            $contact = \Modules\Flick\Models\FlickContact::where('profile_url', $url)->first();
            if ($contact) {
                $nsid = $contact->nsid;
                $this->info("Resolved from DB: $nsid");
            } else {
                // Queue resolution task
                $this->info("Queueing resolution task...");
                \Modules\Flick\Models\FlickCrawlTask::create([
                    'contact_nsid' => 'pending_resolution_' . md5($url), // Temporary ID
                    'type' => 'RESOLVE_USER',
                    'page' => 1,
                    'status' => 'pending',
                    'priority' => 100,
                    'payload' => ['url' => $url, 'original_action' => 'FETCH_CONTACTS']
                ]);
                $this->info("Resolution task queued. Worker will process it.");
                // We continue to worker loop to process this task
                $nsid = null; // Don't create root task yet
            }
        }

        // If NSID is not provided (and not a URL we just queued), we assume we want to crawl the authenticated user's contacts.
        // We create a root task for this.
        // Note: If we don't know the NSID of the authenticated user, we might need to call flickr.test.login first?
        // Or just use 'me' or similar if API supports it.
        // flickr.contacts.getList usually takes no arguments for "me".

        $rootNsid = $nsid ?? 'me';

        if ($nsid || $rootNsid === 'me') { // Only create root task if NSID is known or it's 'me'
            $this->info("Starting crawl for: $rootNsid");

            // Create initial task if not exists
            \Modules\Flick\Models\FlickCrawlTask::firstOrCreate(
                [
                    'contact_nsid' => $rootNsid,
                    'type' => 'FETCH_CONTACTS',
                    'page' => 1,
                ],
                [
                    'status' => 'pending',
                    'priority' => 100, // High priority for root
                ]
            );
        }

        $this->info("Root task created/verified. Starting worker loop...");

        $service = new \Modules\Flick\Services\FlickrHubService();

        while (!$this->shouldExit) {
            $task = null;

            \Illuminate\Support\Facades\DB::transaction(function () use (&$task) {
                $task = \Modules\Flick\Models\FlickCrawlTask::where('status', 'pending')
                    ->orderBy('priority', 'desc')
                    ->orderBy('id', 'asc')
                    ->lockForUpdate() // Lock the row
                    ->first();

                if ($task) {
                    // Mark as processing immediately to prevent others from picking it
                    // But wait, we want to mark it as 'queued_at_hub' only after successful API call?
                    // No, if we don't change status here, lockForUpdate releases after transaction commit.
                    // So we must change status to something intermediate like 'processing' or just keep the transaction open?
                    // Keeping transaction open during API call is bad.
                    // So let's add a 'processing' status or just assume 'queued_at_hub' is the next state.
                    // But we haven't called Hub yet.
                    // Let's stick to 'pending' but maybe we can't fully prevent race condition without an intermediate state.
                    // However, for this simple crawler, maybe just optimistic locking or just accept it.
                    // BETTER: Update status to 'processing' here.
                    // But my migration comment said: pending, queued_at_hub, completed, failed.
                    // I didn't add 'processing'.
                    // Let's just use 'queued_at_hub' as "processing" for now, or assume single worker.
                    // User asked for improvements.
                    // I'll add 'processing' status to the enum in my mind (it's a string column).

                    $task->update(['status' => 'processing']);
                }
            });

            if (!$task) {
                $this->info("No pending tasks. Sleeping...");
                sleep(5);
                continue;
            }

            $this->info("Processing task {$task->id}: {$task->type} for {$task->contact_nsid}" . ($task->page ? " (Page {$task->page})" : ""));

            // Prepare API call
            $method = '';
            $params = [];

            switch ($task->type) {
                case 'FETCH_CONTACTS':
                    $method = 'flickr.contacts.getList'; // Or getPublicList for others
                    if ($task->contact_nsid !== 'me') {
                        $method = 'flickr.contacts.getPublicList';
                        $params['user_id'] = $task->contact_nsid;
                    }
                    $params['page'] = $task->page;
                    $params['per_page'] = 1000;
                    break;
                case 'FETCH_PHOTOS':
                    $method = 'flickr.people.getPublicPhotos'; // Or getPhotos
                    $params['user_id'] = $task->contact_nsid;
                    $params['page'] = $task->page;
                    $params['per_page'] = 500;
                    $params['extras'] = 'date_upload,date_taken,last_update,geo,tags,machine_tags,o_dims,views,media,path_alias,url_sq,url_t,url_s,url_q,url_m,url_n,url_z,url_c,url_l,url_o';
                    break;
                case 'FETCH_FAVES':
                    $method = 'flickr.favorites.getPublicList';
                    $params['user_id'] = $task->contact_nsid;
                    $params['page'] = $task->page;
                    $params['per_page'] = 500;
                    break;
                case 'DOWNLOAD_PHOTOS':
                    $this->info("Starting auto-download for {$task->contact_nsid}");
                    $downloadService = new \Modules\Flick\Services\DownloadService();
                    $count = $downloadService->downloadUserPhotos($task->contact_nsid, false, $this);

                    $contact = \Modules\Flick\Models\FlickContact::where('nsid', $task->contact_nsid)->first();
                    $name = $contact ? ($contact->username ?? $contact->realname ?? $task->contact_nsid) : $task->contact_nsid;

                    $msg = "Contact {$name} ({$task->contact_nsid}) downloaded {$count} new photos.";
                    (new \Modules\Flick\Services\TelegramService())->notify($msg);

                    $task->update(['status' => 'completed']);
                    continue 2; // Skip API call and loop again
                case 'RESOLVE_USER':
                    $method = 'flickr.urls.lookupUser';
                    $params['url'] = $task->payload['url'];
                    break;
            }

            $response = $service->request($method, $params);

            if ($response && isset($response['request_id'])) {
                $task->update([
                    'status' => 'queued_at_hub',
                    'hub_request_id' => $response['request_id'],
                ]);
                $this->info("Task queued at Hub. Request ID: {$response['request_id']}");
            } else {
                $this->error("Failed to queue task at Hub.");
                // Maybe retry count? For now, mark failed to avoid infinite loop
                $task->update(['status' => 'failed']);
            }

            // Simple rate limit / backpressure for the loop
            sleep(1);
        }
    }
}
