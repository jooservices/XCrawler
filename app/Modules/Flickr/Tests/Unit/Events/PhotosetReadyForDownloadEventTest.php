<?php

namespace App\Modules\Flickr\Tests\Unit\Events;

use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Events\PhotosetReadyForDownloadEvent;
use App\Modules\Flickr\Jobs\DownloadPhotoJob;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Services\TaskService;
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
            'task' => TaskService::TASK_DOWNLOAD_PHOTOSET
        ]);

        Event::dispatch(new PhotosetReadyForDownloadEvent($task));

        $this->assertTrue($task->refresh()->isState(InProgressState::class));

        Queue::assertPushed(DownloadPhotoJob::class, 10);
    }
}
