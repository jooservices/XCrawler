<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs\Photoset;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\PhotosetCreatedEvent;
use App\Modules\Flickr\Jobs\PhotosetsJob;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class PhotosetsJobTest extends TestCase
{
    public function testGetList()
    {
        Event::fake(PhotosetCreatedEvent::class);

        $contact = app(FlickrContactService::class)->create([
            'nsid' => '99097633@N00'
        ]);

        $task = $contact->tasks()
            ->where('task', FlickrService::TASK_CONTACT_PHOTOSETS)
            ->first();

        PhotosetsJob::dispatch($this->integration, $task);

        $this->assertDatabaseCount('flickr_photosets', 47);
        $this->assertCount(47, $contact->refresh()->photosets);

        Event::assertDispatchedTimes(PhotosetCreatedEvent::class, 47);
        $this->assertEquals(States::STATE_COMPLETED, $task->refresh()->state_code);
    }
}
