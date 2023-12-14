<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs;

use App\Modules\Flickr\Events\ContactCreatedEvent;
use App\Modules\Flickr\Events\FetchContactsCompletedEvent;
use App\Modules\Flickr\Tests\TestCase;
use App\Modules\Flickr\Jobs\ContactJob;
use Illuminate\Support\Facades\Event;

class ContactJobTest extends TestCase
{
    public function testGetContacts()
    {
        Event::fake([
            ContactCreatedEvent::class,
            FetchContactsCompletedEvent::class,
        ]);

        ContactJob::dispatch($this->integration);

        $this->assertDatabaseCount('flickr_contacts', 1102);

        Event::assertDispatchedTimes(ContactCreatedEvent::class, 1102);
        Event::assertDispatchedTimes(FetchContactsCompletedEvent::class);
        $this->assertEquals(2, $this->integration->refresh()->requested_times);
    }
}
