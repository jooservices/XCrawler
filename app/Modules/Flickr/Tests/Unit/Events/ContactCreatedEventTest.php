<?php

namespace App\Modules\Flickr\Tests\Unit\Events;

use App\Modules\Core\Services\States;
use App\Modules\Core\StateMachine\Task\InitState;
use App\Modules\Flickr\Events\ContactCreatedEvent;
use App\Modules\Flickr\Events\ContactTasksCreatedEvent;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Services\TaskService;
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

        $this->assertCount(count(TaskService::CONTACT_TASKS), $contact->tasks);

        foreach (FlickrService::CONTACT_TASKS as $index => $task) {
            $this->assertEquals($task, $contact->tasks[$index]->task);
            $this->assertEquals(InitState::class, $contact->tasks[$index]->state_code);
        }

        Event::assertDispatched(ContactTasksCreatedEvent::class);
    }
}
