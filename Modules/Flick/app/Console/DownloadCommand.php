<?php

namespace Modules\Flick\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DownloadCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'flick:download {nsid : The NSID of the user to download photos for} {--force : Force download even if exists} {--limit= : Limit number of photos to download}';

    protected $description = 'Download all photos for a specific user';

    public function handle()
    {
        $nsid = $this->argument('nsid');
        $force = $this->option('force');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $service = new \Modules\Flick\Services\DownloadService();
        $count = $service->downloadUserPhotos($nsid, $force, $this, $limit);

        // Telegram Notification
        $contact = \Modules\Flick\Models\FlickContact::where('nsid', $nsid)->first();
        $name = $contact ? ($contact->username ?? $contact->realname ?? $nsid) : $nsid;

        $msg = "Contact {$name} ({$nsid}) downloaded {$count} new photos.";
        (new \Modules\Flick\Services\TelegramService())->notify($msg);
        $this->info("Telegram notification sent.");
    }

    protected function getBestUrl($photo)
    {
        // Check sizes_json first (populated from extras)
        if ($photo->sizes_json) {
            $sizes = $photo->sizes_json;
            // Priority: Original > Large 2048 > Large 1600 > Large > Medium 800 > Medium 640
            if (isset($sizes['original']))
                return $sizes['original'];
            if (isset($sizes['large_2048']))
                return $sizes['large_2048'];
            if (isset($sizes['large_1600']))
                return $sizes['large_1600'];
            if (isset($sizes['large']))
                return $sizes['large'];
            if (isset($sizes['medium_800']))
                return $sizes['medium_800'];
            if (isset($sizes['medium_640']))
                return $sizes['medium_640'];
        }

        // Fallback
        return "https://live.staticflickr.com/{$photo->server}/{$photo->flickr_id}_{$photo->secret}_b.jpg";
    }
}
