<?php

namespace App\Modules\Flickr\Tests\Unit\Events;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\ContactCreatedEvent;
use App\Modules\Flickr\Events\ContactTasksCreatedEvent;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class ContactCreatedEventTest extends TestCase
{
    public function testEventDispatched()
    {
        Event::fake(ContactTasksCreatedEvent::class);
        $contact = FlickrContact::factory()->create();

        /**
         * Make sure event will create required tasks.
         */
        Event::dispatch(new ContactCreatedEvent($contact));


        $this->assertCount(count(FlickrService::TASKS), $contact->tasks);
        foreach (FlickrService::TASKS as $index => $task) {
            $this->assertEquals($task, $contact->tasks[$index]->task);
            $this->assertEquals(States::STATE_INIT, $contact->tasks[$index]->state_code);
        }

        Event::assertDispatched(ContactTasksCreatedEvent::class);
    }
}
