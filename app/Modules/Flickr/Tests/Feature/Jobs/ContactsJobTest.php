<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs;

use App\Modules\Flickr\Events\ContactCreatedEvent;
use App\Modules\Flickr\Events\FetchContactsCompletedEvent;
use App\Modules\Flickr\Events\FetchContactsRecursiveEvent;
use App\Modules\Flickr\Jobs\ContactsJob;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class ContactsJobTest extends TestCase
{
    public function testGetContacts()
    {
        Event::fake([
            ContactCreatedEvent::class,
            FetchContactsCompletedEvent::class,
            FetchContactsRecursiveEvent::class
        ]);

        ContactsJob::dispatch($this->integration);

        $this->assertDatabaseCount('flickr_contacts', 1102);

        Event::assertDispatchedTimes(ContactCreatedEvent::class, 1102);
        Event::assertDispatchedTimes(FetchContactsCompletedEvent::class);
        Event::assertDispatchedTimes(FetchContactsRecursiveEvent::class);
        $this->assertEquals(2, $this->integration->refresh()->requested_times);
    }
}
