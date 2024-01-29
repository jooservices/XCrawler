<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\StateMachine\Integration\CompletedState;
use App\Modules\Flickr\Database\factories\PhotoFactory;
use App\Modules\Flickr\Events\PhotoSizedEvent;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\PermissionDeniedException;
use App\Modules\Flickr\Jobs\PhotosizesJob;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class PhotoSizesJobTest extends TestCase
{
    public function testGetPhotoSizes()
    {
        Event::fake(PhotoSizedEvent::class);
        $photo = FlickrPhoto::factory()->create([
            'id' => PhotoFactory::ID_WITH_SIZES
        ]);

        PhotosizesJob::dispatch($this->integration, $photo)
            ->onQueue(FlickrService::QUEUE_NAME);

        $this->assertIsArray($photo->refresh()->sizes);
        $this->assertEquals(13, count($photo->sizes));

        Event::assertDispatched(PhotoSizedEvent::class, function ($event) use ($photo) {
            return $event->photo->id === $photo->id;
        });
    }

    public function testGetPhotoSizesNotFound()
    {
        Event::fake(PhotoSizedEvent::class);
        $photo = FlickrPhoto::factory()->create([
            'id' => 1
        ]);

        $this->expectException(FailedException::class);
        PhotosizesJob::dispatch($this->integration, $photo)->onQueue(FlickrService::QUEUE_NAME);
        $this->assertTrue($photo->refresh()->trashed());
    }

    public function testGetPhotoSizesPermissionDenied()
    {
        $integration = Integration::factory()->create([
            'service' => FlickrService::SERVICE_NAME,
            'state_code' => CompletedState::class,
            'is_primary' => false,
        ]);

        Event::fake(PhotoSizedEvent::class);
        $photo = FlickrPhoto::factory()->create([
            'id' => 3
        ]);

        $this->expectException(PermissionDeniedException::class);
        $this->expectException(InvalidRespondException::class);
        PhotosizesJob::dispatch($integration, $photo)->onQueue(FlickrService::QUEUE_NAME);

        $this->assertTrue($photo->refresh()->trashed());
    }
}
