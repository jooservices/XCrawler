<?php

namespace App\Modules\Flickr\Tests\Unit\Events;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\PhotosetReadyForDownloadEvent;
use App\Modules\Flickr\Jobs\DownloadPhotoJob;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

class PhotosetReadyForDownloadEventTest extends TestCase
{
    public function testEvent()
    {
        Queue::fake(DownloadPhotoJob::class);

        $photoset = FlickrPhotoset::factory()->create();
        $photoset->relationshipPhotos()->createMany(
            FlickrPhotoset::factory()->count(10)->make()->toArray()
        );

        $task = $photoset->tasks()->create([
            'task' => FlickrService::TASK_DOWNLOAD_PHOTOSET
        ]);

        Event::dispatch(new PhotosetReadyForDownloadEvent($task));

        $this->assertEquals(
            States::STATE_IN_PROGRESS,
            $task->refresh()->state_code
        );

        Queue::assertPushed(DownloadPhotoJob::class, 10);
    }
}
