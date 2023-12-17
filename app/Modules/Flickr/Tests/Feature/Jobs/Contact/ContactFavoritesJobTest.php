<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs\Contact;

use App\Modules\Flickr\Events\ContactCreatedEvent;
use App\Modules\Flickr\Jobs\ContactFavoritesJob;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class ContactFavoritesJobTest extends TestCase
{
    public function testGetPeopleFavorites()
    {
        Event::fake(ContactCreatedEvent::class);

        /**
         * This user have 1487 favorites.
         */
        $nsid = '94529704@N02';

        $this->assertDatabaseCount('flickr_photos', 0);
        $this->assertDatabaseCount('flickr_contacts', 0);

        $this->assertEquals(0, (int)$this->integration->refresh()->requested_times);
        ContactFavoritesJob::dispatch($this->integration, $nsid);
        Event::assertDispatchedTimes(ContactCreatedEvent::class, 350);

        $this->assertDatabaseCount('flickr_photos', 1487);
        $this->assertDatabaseCount('flickr_contacts', 350);
    }
}
