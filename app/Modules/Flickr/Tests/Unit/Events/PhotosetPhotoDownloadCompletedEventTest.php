<?php

namespace App\Modules\Flickr\Tests\Unit\Events;

use App\Modules\Client\Services\GooglePhotos;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\PhotosetPhotoDownloadCompletedEvent;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;

class PhotosetPhotoDownloadCompletedEventTest extends TestCase
{
    public function testWhenNotFinishedDownloadAllPhotos()
    {
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
            'task' => FlickrService::TASK_DOWNLOAD_PHOTOSET,
            'state_code' => States::STATE_INIT,
            'payload' => [
                'photos' => 1
            ]
        ]);

        $subTask = $task->subTasks()->create([
            'task' => FlickrService::TASK_DOWNLOAD_PHOTOSET_PHOTO,
            'state_code' => States::STATE_INIT,
            'model_id' => $photo->id,
            'model_type' => FlickrPhoto::class,
        ]);

        $this->instance(
            GooglePhotos::class,
            Mockery::mock(GooglePhotos::class, function (MockInterface $mock) {
                $mock->shouldReceive('createPhoto');
            })
        );

        Event::dispatch(new PhotosetPhotoDownloadCompletedEvent($subTask));

        $this->assertEquals(States::STATE_DOWNLOADED, $subTask->refresh()->state_code);
        $this->assertEquals(States::STATE_COMPLETED, $task->refresh()->state_code);
    }
}
