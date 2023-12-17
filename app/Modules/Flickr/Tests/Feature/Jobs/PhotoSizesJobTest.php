<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\PhotoSizedEvent;
use App\Modules\Flickr\Jobs\PhotoSizesJob;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class PhotoSizesJobTest extends TestCase
{
    public function testGetPhotoSizes()
    {
        Event::fake(PhotoSizedEvent::class);
        $photo = FlickrPhoto::factory()->create();

        PhotoSizesJob::dispatch($this->integration, $photo)->onQueue(FlickrService::QUEUE_NAME);

        $this->assertIsArray($photo->refresh()->sizes);
        $this->assertEquals(13, count($photo->sizes));

        Event::assertDispatched(PhotoSizedEvent::class, function ($event) use ($photo) {
            return $event->photo->id === $photo->id;
        });
    }
}
