<?php

namespace App\Modules\Flickr\Tests\Unit\Events;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\Services\GooglePhotos;
use App\Modules\Client\StateMachine\Integration\CompletedState;
use App\Modules\Core\StateMachine\Task\CompletedState as TaskCompletedState;
use App\Modules\Core\StateMachine\Task\InProgressState;
use App\Modules\Flickr\Console\Download\PhotoUploadCommand;
use App\Modules\Flickr\Events\PhotosetPhotoReadyForUploadEvent;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Services\TaskService;
use App\Modules\Flickr\Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;

class PhotosetPhotoReadyForUploadEventTest extends TestCase
{
    public function testEvent()
    {
        Integration::factory()->create([
            'service' => GooglePhotos::SERVICE_NAME,
            'state_code' => CompletedState::class
        ]);

        $this->instance(
            GooglePhotos::class,
            Mockery::mock(Client::class, function (MockInterface $mock) {
                $mock->shouldReceive('createPhoto')
                    ->andReturn();
            })
        );

        $photoset = FlickrPhotoset::factory()->create();
        FlickrPhoto::factory()->count(5)->create([
            'sizes' => [
                [
                    'label' => 'Original',
                    'source' => 'https://live.staticflickr.com/65535/51343589772_5b7b7e7b9a_o.jpg',
                    'url' => 'https://www.flickr.com/photos/191387775@N06/51343589772/sizes/o/',
                    'media' => 'photo',
                ]
            ]
        ]);
        $photoset->relationshipPhotos()->syncWithoutDetaching(FlickrPhoto::all()->pluck('id')->toArray());

        $photoset->googlePhotoAlbum()->create([
            'title' => $this->faker->title,
            'album_id' => $this->faker->uuid
        ]);

        Event::dispatch(new PhotosetPhotoReadyForUploadEvent($photoset));
        $task = $photoset->tasks()->where('task', TaskService::TASK_UPLOAD_PHOTOSET)->first();
        $this->assertTrue($task->isState(InProgressState::class));
        $this->assertEquals(5, $task->payload['photos']);
        $this->assertEquals(5, $task->subTasks()->count());

        $this->artisan(PhotoUploadCommand::COMMAND)->assertExitCode(0);

        $task = $photoset->tasks()->where('task', TaskService::TASK_UPLOAD_PHOTOSET)->first();

        $this->assertEquals(5, $task->subTasks()->where('state_code', TaskCompletedState::class)->count());
        $this->assertTrue($task->isState(TaskCompletedState::class));
    }
}
