<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs;

use App\Modules\Flickr\Events\ContactCreatedEvent;
use App\Modules\Flickr\Events\FetchContactsCompletedEvent;
use App\Modules\Flickr\Events\FetchContactsRecursiveEvent;
use App\Modules\Flickr\Jobs\ContactsJob;
use App\Modules\Flickr\Jobs\DownloadPhotoJob;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class DownloadPhotoJobTest extends TestCase
{
    public function testDownload()
    {
        $photo = FlickrPhoto::factory()->create([
            'sizes' => [
                [
                    'source' => 'https://live.staticflickr.com//65535//53312842788_9831c0d67a_s.jpg'
                ]
            ]
        ]);

        $task = $photo->tasks()->create([
            'task' => FlickrService::TASK_DOWNLOAD_PHOTOSET_PHOTO
        ]);

        DownloadPhotoJob::dispatch($task);
    }
}
