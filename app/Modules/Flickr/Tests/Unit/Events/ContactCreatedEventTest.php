<?php

namespace App\Modules\Flickr\Tests\Unit\Events;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\ContactCreatedEvent;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class ContactCreatedEventTest extends TestCase
{
    public function testEventDispatched()
    {
        $contact = FlickrContact::factory()->create();

        Event::dispatch(new ContactCreatedEvent($contact));

        $this->assertCount(2, $contact->tasks);
        $this->assertEquals(FlickrService::TASK_CONTACT_FAVORITES, $contact->tasks[0]->task);
        $this->assertEquals(States::STATE_INIT, $contact->tasks[0]->state_code);
        $this->assertEquals(FlickrService::TASK_CONTACT_PHOTOS, $contact->tasks[1]->task);
        $this->assertEquals(States::STATE_INIT, $contact->tasks[1]->state_code);
    }
}
