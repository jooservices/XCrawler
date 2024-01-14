<?php

namespace App\Modules\Flickr\Tests\Unit\Events;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\PhotosetCreatedEvent;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class PhotosetCreatedEventTest extends TestCase
{
    public function testEventDispatched()
    {
        $photoset = FlickrPhotoset::factory()->create();

        /**
         * Make sure event will create required tasks.
         */
        Event::dispatch(new PhotosetCreatedEvent($photoset));

        $this->assertCount(1, $photoset->tasks);
    }
}
