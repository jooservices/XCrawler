<?php

namespace App\Flickr\Tests\Feature\Command;

use App\Flickr\Jobs\ContactInfoJob;
use App\Flickr\Jobs\GetFavoritePhotosJob;
use App\Flickr\Tests\AbstractFlickrTest;
use App\Models\FlickrContact;
use App\Services\Flickr\FlickrService;
use Illuminate\Support\Facades\Queue;

class ContactInfoTest extends AbstractFlickrTest
{
    public function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->buildMock(true);
        $this->service = app(FlickrService::class);
    }

    public function test_get_contact_info()
    {
        /**
         * From now whenever contact is created it will also trigger job to get detail info
         * We do fake event here to prevent that
         * Beside that this command used for relooping when all contact info are updated
         */

        $contact = FlickrContact::factory()->create([
            'nsid' => '124830340@N02',
            'state_code' => FlickrContact::STATE_MANUAL
        ]);
        $this->artisan('flickr:contact-info');

        Queue::assertPushed(ContactInfoJob::class, function ($event) use ($contact) {
            return $event->contact->nsid = $contact->nsid;
        });

        Queue::assertPushed(GetFavoritePhotosJob::class, function ($event) use ($contact) {
            return $event->contact->nsid = $contact->nsid;
        });
    }

    public function test_get_contact_info_empty()
    {
        $this->artisan('flickr:contact-info');
        Queue::assertNothingPushed();
    }
}
