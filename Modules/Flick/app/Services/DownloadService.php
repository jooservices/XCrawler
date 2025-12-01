<?php

namespace Modules\Flick\Services;

use Modules\Flick\Models\FlickPhoto;
use Illuminate\Support\Facades\Log;

class DownloadService
{
    public function downloadUserPhotos(string $nsid, bool $force = false, $output = null, ?int $limit = null)
    {
        $query = FlickPhoto::where('owner_nsid', $nsid)
            ->whereNull('deleted_at');

        // If limit is set, prioritize undownloaded photos
        if ($limit) {
            $query->whereNull('downloaded_at')->take($limit);
        }

        $photos = $query->get();

        $count = 0;
        $total = $photos->count();
        $downloaded = 0;

        if ($output) {
            $output->info("Starting download for {$nsid}. Total: {$total}");
        }

        foreach ($photos as $photo) {
            $dir = base_path("media/{$nsid}/photos");
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            $url = $this->getBestUrl($photo);
            if (!$url)
                continue;

            $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $filename = "{$photo->flickr_id}_{$photo->secret}.{$ext}";
            $path = "{$dir}/{$filename}";

            if (file_exists($path) && !$force) {
                if (!$photo->is_downloaded) {
                    $photo->update(['is_downloaded' => true, 'local_path' => $path]);
                }
                continue;
            }

            try {
                $content = file_get_contents($url);
                if ($content) {
                    file_put_contents($path, $content);
                    $photo->update(['is_downloaded' => true, 'local_path' => $path]);
                    $downloaded++;
                }
            } catch (\Exception $e) {
                Log::error("Download failed for {$photo->flickr_id}: " . $e->getMessage());
            }
        }

        if ($output) {
            $output->info("Downloaded {$downloaded} new photos.");
        }

        return $downloaded;
    }

    protected function getBestUrl($photo)
    {
        if ($photo->sizes_json) {
            $sizes = $photo->sizes_json;
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
        return "https://live.staticflickr.com/{$photo->server}/{$photo->flickr_id}_{$photo->secret}_b.jpg";
    }
}
