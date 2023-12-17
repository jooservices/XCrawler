<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs\Photoset;

use App\Modules\Flickr\Events\PhotosetCreatedEvent;
use App\Modules\Flickr\Jobs\PhotosetsJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class PhotosetsJobTest extends TestCase
{
    public function testGetList()
    {
        Event::fake(PhotosetCreatedEvent::class);
        $contact = FlickrContact::factory()->create([
            'nsid' => '99097633@N00'
        ]);

        PhotosetsJob::dispatch($this->integration, $contact->nsid);

        $this->assertDatabaseCount('flickr_photosets', 47);
        $this->assertCount(47, $contact->refresh()->photosets);

        Event::assertDispatchedTimes(PhotosetCreatedEvent::class, 47);
    }
}
