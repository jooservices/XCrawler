<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Jobs\ContactPhotosJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Tests\TestCase;

class ContactPhotosJobTest extends TestCase
{
    public function testGetPeoplePhotos()
    {
        $contact = FlickrContact::create(['nsid' => '73115043@N07',]);

        ContactPhotosJob::dispatch($this->integration, '73115043@N07');

        $this->assertDatabaseCount('flickr_photos', 507,);
        $this->assertEquals(507, $contact->photos()->count());
    }
}
