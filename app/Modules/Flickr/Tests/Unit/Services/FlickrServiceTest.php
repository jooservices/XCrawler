<?php

namespace App\Modules\Flickr\Tests\Unit\Services;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Events\BeforeProcessContact;
use App\Modules\Flickr\Jobs\FlickrContacts;
use App\Modules\Flickr\Jobs\FlickrFavorites;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

class FlickrServiceTest extends TestCase
{
    private FlickrService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = app(FlickrService::class);
    }

    public function testContacts()
    {
        Queue::fake(FlickrContacts::class);

        $this->service->contacts();

        Queue::assertPushed(FlickrContacts::class, function ($job) {
            return $job->page === 2;
        });
    }

    public function testContactFavorites()
    {
        Event::fake(BeforeProcessContact::class);
        Queue::fake(FlickrFavorites::class);

        $contact = FlickrContact::create([
            'nsid' => '123',
            'username' => 'test',
        ]);

        $this->service->contactFavorites();

        Event::assertDispatched(BeforeProcessContact::class, function ($event) use ($contact) {
            return $event->contact->is($contact);
        });

        $this->assertEquals(States::STATE_IN_PROGRESS, $contact->fresh()->favorites_state_code);

        Queue::assertPushed(FlickrFavorites::class, function ($job) use ($contact) {
            return $job->nsid === $contact->nsid;
        });
    }
}
