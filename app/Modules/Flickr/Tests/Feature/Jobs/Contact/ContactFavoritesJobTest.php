<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs\Contact;

use App\Modules\Flickr\Events\ContactCreatedEvent;
use App\Modules\Flickr\Jobs\ContactFavoritesJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class ContactFavoritesJobTest extends TestCase
{
    public function testGetPeopleFavorites()
    {


        $this->assertDatabaseCount('flickr_photos', 0);
        $this->assertDatabaseCount('flickr_contacts', 0);

        /**
         * This user have 1487 favorites.
         */
        $contact = app(FlickrContactService::class)->create(['nsid' => '94529704@N02']);

        $this->assertEquals(0, (int)$this->integration->refresh()->requested_times);
        $this->assertEquals(3, $contact->refresh()->tasks->count());

        Event::fake(ContactCreatedEvent::class);

        $task = $contact->refresh()->tasks()->where('task', FlickrService::TASK_CONTACT_FAVORITES)->first();

        ContactFavoritesJob::dispatch($this->integration, $task);

        Event::assertDispatchedTimes(ContactCreatedEvent::class, 350);

        $this->assertDatabaseCount('flickr_photos', 1487);
        $this->assertDatabaseCount('flickr_contacts', 351);

        // Whenever task is fetched, it should be deleted.
        $this->assertCount(0, $contact->refresh()
            ->tasks()
            ->where('task', FlickrService::TASK_CONTACT_FAVORITES)->get());
    }
}
