<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs\Photoset;

use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Events\FetchPhotosetPhotosCompletedEvent;
use App\Modules\Flickr\Events\RecurredTaskEvent;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Jobs\PhotosetPhotosJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\TaskService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class PhotosJobTest extends TestCase
{
    public function testGetPhotosetPhotos()
    {
        Event::fake([
            FetchPhotosetPhotosCompletedEvent::class,
            RecurredTaskEvent::class,
        ]);
        $contact = FlickrContact::factory()->create([
            'nsid' => '94529704@N02',
        ]);

        $photoset = $contact->photosets()->create([
            'id' => '72157674594210788',
        ]);

        $task = $photoset->tasks()->create([
            'task' => TaskService::TASK_PHOTOSET_PHOTOS,
        ]);
        $task->transitionTo(InProgressState::class);

        PhotosetPhotosJob::dispatch($this->integration, $task);

        $this->assertEquals(1, $photoset->refresh()->relationshipPhotos->count());
        Event::assertDispatched(FetchPhotosetPhotosCompletedEvent::class);
    }

    public function testGetPhotosetsPhotoNotFound()
    {
        $contact = FlickrContact::factory()->create([
            'nsid' => '94529704@N02',
        ]);

        $photoset = $contact->photosets()->create([
            'id' => 1,
        ]);

        $task = $photoset->tasks()->create([
            'task' => TaskService::TASK_PHOTOSET_PHOTOS,
        ]);
        $task->transitionTo(InProgressState::class);

        $this->expectException(FailedException::class);
        PhotosetPhotosJob::dispatch($this->integration, $task);

        $this->assertDatabaseMissing('flickr_photos', [
            'id' => 1,
        ]);

        $this->assertTrue($task->isFailedState());
    }

    public function testGetPhotosetsPhotoUserNotFound()
    {
        $contact = FlickrContact::factory()->create([
            'nsid' => '94529704@N02',
        ]);

        $photoset = $contact->photosets()->create([
            'id' => 2,
        ]);

        $task = $photoset->tasks()->create([
            'task' => TaskService::TASK_PHOTOSET_PHOTOS,
        ]);
        $task->transitionTo(InProgressState::class);

        $this->expectException(FailedException::class);
        PhotosetPhotosJob::dispatch($this->integration, $task);

        $this->assertDatabaseMissing('flickr_contacts', [
            'nsid' => '94529704@N02',
        ]);
        $this->assertDatabaseMissing('flickr_photos', [
            'id' => 1,
        ]);

        $this->assertTrue($task->isFailedState());
    }
}
