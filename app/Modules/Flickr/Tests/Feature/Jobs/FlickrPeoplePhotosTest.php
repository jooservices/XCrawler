<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\Tests\TestCase;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Jobs\FlickrPhotos;
use App\Modules\Flickr\Models\FlickrContacts;

class FlickrPeoplePhotosTest extends TestCase
{
    public function testGetPeoplePhotos()
    {
        FlickrContacts::truncate();
        \App\Modules\Flickr\Models\FlickrPhotos::truncate();
        Integration::truncate();
        Integration::factory()->create();

        $contact = FlickrContacts::create(['nsid' => '73115043@N07',]);

        FlickrPhotos::dispatch('73115043@N07');

        $this->assertDatabaseCount('flickr_photos', 507, 'mongodb');
        $this->assertEquals(States::STATE_COMPLETED, $contact->fresh()->state_code);
    }
}
