<?php

namespace App\Modules\Flickr\Tests\Unit\Services;

use App\Modules\Flickr\Events\ContactCreatedEvent;
use App\Modules\Flickr\Events\ContactTasksCreatedEvent;
use App\Modules\Flickr\Services\FlickrContactService;
use App\Modules\Flickr\Services\TaskService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class FlickrContactServiceTest extends TestCase
{
    public function testCreateContact()
    {
        Event::fake(ContactCreatedEvent::class);
        $contact = app(FlickrContactService::class)->create(['nsid' => $this->faker()->uuid,]);

        Event::assertDispatched(ContactCreatedEvent::class, function ($event) use ($contact) {
            return $event->contact->is($contact);
        });
    }

    public function testCreateContactDuplicate()
    {
        Event::fake(ContactCreatedEvent::class);
        $contact = app(FlickrContactService::class)->create(['nsid' => $this->faker()->uuid,]);
        app(FlickrContactService::class)->create(['nsid' => $contact->nsid,]);

        Event::assertDispatchedTimes(ContactCreatedEvent::class);
        $this->assertDatabaseCount('flickr_contacts', 1);
    }

    public function testAddPhotos()
    {
        $photo = [
            'owner' => $this->faker->uuid,
            'id' => $this->faker->numerify()
        ];

        app(FlickrContactService::class)->addPhotos(collect([$photo]));

        $this->assertDatabaseHas('flickr_photos', ['id' => $photo['id'],]);
        $this->assertDatabaseHas('flickr_contacts', ['nsid' => $photo['owner'],]);
    }

    public function testCreateContactAndTasksCreated()
    {
        Event::fake(ContactTasksCreatedEvent::class);
        $contact = app(FlickrContactService::class)->create(['nsid' => $this->faker()->uuid,]);

        $this->assertEquals(count(TaskService::CONTACT_TASKS), $contact->tasks()->count());
        Event::assertDispatched(ContactTasksCreatedEvent::class);
    }
}
