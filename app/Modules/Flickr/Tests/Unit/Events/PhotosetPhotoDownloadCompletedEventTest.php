<?php

namespace App\Modules\Flickr\Tests\Unit\Events;

use App\Modules\Client\Services\GooglePhotos;
use App\Modules\Core\StateMachine\Task\DownloadedState;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Events\PhotosetPhotoDownloadCompletedEvent;
use App\Modules\Flickr\Events\PhotosetPhotoReadyForUploadEvent;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Services\TaskService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;

class PhotosetPhotoDownloadCompletedEventTest extends TestCase
{
    public function testWhenPhotosetPhotosDownloaded()
    {
        Event::fake([
            PhotosetPhotoReadyForUploadEvent::class,
        ]);
        $photoset = FlickrPhotoset::factory()->create();
        $photo = FlickrPhoto::factory()->create([
            'sizes' => [
                [
                    'label' => 'Original',
                    'source' => 'https://live.staticflickr.com/65535/51343589772_5b7b7e7b9a_o.jpg',
                    'url' => 'https://www.flickr.com/photos/191387775@N06/51343589772/sizes/o/',
                    'media' => 'photo',
                ]
            ]
        ]);
        $photoset->relationshipPhotos()->attach($photo);
        $photoset->googlePhotoAlbum()->create([
            'album_id' => $this->faker->uuid,
            'title' => 'Test',
        ]);

        $task = $photoset->tasks()->create([
            'task' => TaskService::TASK_DOWNLOAD_PHOTOSET,
            'payload' => [
                'photos' => 1
            ]
        ]);
        // This task state transited in PhotosetReadyForDownloadEvent event
        $task->transitionTo(InProgressState::class);

        $subTask = $task->subTasks()->create([
            'task' => TaskService::TASK_DOWNLOAD_PHOTOSET_PHOTO,
            'model_id' => $photo->id,
            'model_type' => FlickrPhoto::class,
        ]);

        $this->instance(
            GooglePhotos::class,
            Mockery::mock(GooglePhotos::class, function (MockInterface $mock) {
                $mock->shouldReceive('createPhoto');
            })
        );
        // Transited in DownloadPhotoJob
        $subTask->transitionTo(InProgressState::class);

        Event::dispatch(new PhotosetPhotoDownloadCompletedEvent($subTask));

        $this->assertTrue($subTask->refresh()->isState(DownloadedState::class));
        $this->assertTrue($task->refresh()->isState(DownloadedState::class));
        Event::assertDispatched(PhotosetPhotoReadyForUploadEvent::class);
    }
}
