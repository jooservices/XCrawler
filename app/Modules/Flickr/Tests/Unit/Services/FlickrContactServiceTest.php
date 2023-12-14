<?php

namespace App\Modules\Flickr\Tests\Unit\Services;

use App\Modules\Flickr\Events\ContactCreatedEvent;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class FlickrContactServiceTest extends TestCase
{
    public function testCreateContact()
    {
        Event::fake(ContactCreatedEvent::class);
        $contact = app(FlickrContactService::class)->create([
            'nsid' => $this->faker()->uuid,
        ]);

        Event::assertDispatched(ContactCreatedEvent::class, function ($event) use ($contact) {
            return $event->contact->is($contact);
        });
    }
}
