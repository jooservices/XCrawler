<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs\Photoset;

use App\Modules\Core\Events\RecurredTaskEvent;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Events\Exceptions\PhotosetNotFoundEvent;
use App\Modules\Flickr\Events\FetchPhotosetPhotosCompletedEvent;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\God\Providers\AbstractProvider;
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
            'nsid' => AbstractProvider::NSID
        ]);

        $photoset = $contact->photosets()->create([
            'id' => '72157674594210788',
        ]);

        $task = $photoset->tasks()->create([
            'task' => TaskService::TASK_PHOTOSET_PHOTOS,
        ]);

        PhotosetPhotosJob::dispatch($this->integration, $task);

        $this->assertEquals(2, $photoset->refresh()->relationshipPhotos->count());
        Event::assertDispatched(FetchPhotosetPhotosCompletedEvent::class);
    }

    public function testGetPhotosetsPhotoNotFound()
    {
        $contact = FlickrContact::factory()->create([
            'nsid' => AbstractProvider::NSID
        ]);

        $photoset = $contact->photosets()->create([
            'id' => 1,
        ]);

        $task = $photoset->tasks()->create([
            'task' => TaskService::TASK_PHOTOSET_PHOTOS,
        ]);

        $this->expectException(FailedException::class);
        PhotosetPhotosJob::dispatch($this->integration, $task);

        $this->assertDatabaseMissing('flickr_photosets', ['id' => 1]);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        $this->assertTrue($task->isFailedState());
    }

    public function testGetPhotosetsPhotoUserNotFound()
    {
        Event::fake(PhotosetNotFoundEvent::class);

        $contact = FlickrContact::factory()->create([
            'nsid' => AbstractProvider::NSID
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

        $this->assertDatabaseMissing('flickr_contacts', [
            'nsid' => AbstractProvider::NSID
        ]);
        $this->assertDatabaseMissing('flickr_photos', [
            'id' => 1,
        ]);

        Event::assertDispatched(PhotosetNotFoundEvent::class);
        $this->assertTrue($task->isFailedState());
    }

    public function testGetPhotosetPhotoUserNotFound()
    {
        $contact = FlickrContact::factory()->create([
            'nsid' => AbstractProvider::NSID
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
            'nsid' => AbstractProvider::NSID
        ]);
        $this->assertDatabaseMissing('flickr_photos', [
            'id' => 1,
        ]);

        $this->assertTrue($task->isFailedState());
    }
}
