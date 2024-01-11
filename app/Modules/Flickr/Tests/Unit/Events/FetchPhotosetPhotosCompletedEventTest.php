<?php

namespace App\Modules\Flickr\Tests\Unit\Events;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\FetchPhotosetPhotosCompletedEvent;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class FetchPhotosetPhotosCompletedEventTest extends TestCase
{
    public function testHaveNoParentTask()
    {
        $photoset = FlickrPhotoset::factory()->create();
        $task = $photoset->tasks()->create([
            'task' => FlickrService::TASK_CONTACT_PHOTOSETS,
            'state_code' => States::STATE_INIT
        ]);

        Event::dispatch(new FetchPhotosetPhotosCompletedEvent($task));

        $this->assertEquals(States::STATE_COMPLETED, $task->fresh()->state_code);
    }
}
